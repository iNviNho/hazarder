<?php

namespace App;

use App\Events\UserLogEvent;
use BCMathExtended\BC;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\Model;
use Sunra\PhpSimple\HtmlDomParser;

class UserTicket extends Model
{

    protected $table = "user_tickets";

    /**
     * Get the comments for the blog post.
     */
    public function ticket()
    {
        return $this->belongsTo('App\Ticket');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Return save link for concrete betting site
     * @param $bettingProviderID
     * @return string
     */
    public function getLinkToBettingSite($bettingProviderID) {

        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            $externalTicketId = $this->external_ticket_id;

            // some weird pattern replace
            $externalTicketId = str_replace("%2F", "_", $externalTicketId);
            $externalTicketId = str_replace("%2B", "-", $externalTicketId);

            $link = env("BASE_TICKET_SHOW") . $externalTicketId . "&kind=MAIN";

            return $link;

        } elseif ($bettingProviderID == BettingProvider::SECOND_PROVIDER_N) {

            $externalTicketId = $this->external_ticket_id;

            $link = env("BASE_TICKET_SHOW_SECOND_BETTING_PROVIDER") . $externalTicketId . "?lang=sk";

            return $link;

        }
    }

    /**
     * Lets bet this ticket
     */
    public function bet() {

        // for local environment, don't bet for real
        if (env("APP_ENV") == "local") {
            $this->lamebet();
            return;
        }

        // lets surround all bet logic in try catch
        try {
            $this->realbet();
        } catch(\Throwable $e) {

            event(new UserLogEvent("Failed bet for the first time while betting UserTicket with ID: " . $this->id . " Exception: " . $e->getMessage(), $this->user->id, $this->id));

            // lets try only one more time
            try {

                // and try ONLY if it failed before bet POST request which would set status to "bet"
                if ($this->status != "bet") {
                    $this->realbet();
                } else {
                    event(new UserLogEvent("Failed bet for the second time while betting UserTicket with ID: " . $this->id . " but bet was successfully placed!", $this->user->id));
                }
            } catch(\Throwable $e) {
                event(new UserLogEvent("Failed bet for the second time while betting UserTicket with ID: " . $this->id . " Exception: " . $e->getMessage(), $this->user->id, $this->id));
                if (env("SENTRY_LARAVEL_SHOULD_REPORT")) {
                    $client = new \Raven_Client(env("SENTRY_LARAVEL_DSN"));
                    $client->captureException($e);
                }
                return;
            }
        }
    }

    /**
     * Perform a real bet baby!
     */
    private function realbet() {

        $bettingProviderID = $this->ticket->match->betting_provider_id;
        $now = Carbon::now()->getTimestamp();

        // lets try to login user and have him prepared for betting
        $user = new \App\Services\User\User();
        if (!$user->login($this->user, $bettingProviderID)) {
            // one more time
            if (!$user->login($this->user, $bettingProviderID)) {
                event(new UserLogEvent("Failed login while betting UserTicket with ID: " . $this->id . " for betting provider: " . $bettingProviderID, $this->user->id, $this->id));
                return;
            }

        }
        $guzzleClient = $user->getGuzzleForUserAndBP($this->user, $bettingProviderID);


        // different betting tactic for different betting provider
        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            // clear ticket & have fresh one
            $guzzleClient->get(env("BASE_TICKET_CLEAR_URL") . $now);

            // add to basket
            $res = $guzzleClient->get(env("BASE_BET_URL") . $this->ticket->matchbet->datainfo . "&tip_id=" . $this->ticket->matchbet->dataodd . "&value=" . trim($this->bet_rate) . "&kind=MAIN&_ts=" . $now);

            // lets check if there is not error
            $body = $res->getBody()->getContents();
            $exists = strpos($body, "errors");
            // there is error!
            if ($exists !== false) {
                $this->status = "canceled";
                $this->save();

                event(new UserLogEvent("Failed bet of UserTicket with ID: " . $this->id . " for game type: " . $this->ticket->game_type .
                    "Add to basket failed with error: ". json_encode($body)
                    , $this->user->id, $this->id));

                return;
            }

            // set stake and reset $now
            $now = Carbon::now()->getTimestamp();
            $guzzleClient->get(env("BASE_SET_STAKE_URL") . "&_ts=" . $now . "&stake=" . trim($this->bet_amount));

            // get ticket
            $bettingTicket = $guzzleClient->get(env("BASE_TICKET_URL") . $now);

            $bettingTicketResponse = $bettingTicket->getBody()->getContents();

            $tiketresponse = str_replace("\\n", "", $bettingTicketResponse);
            preg_match("/&transaction_id=(.*?)\"/", $tiketresponse, $matches);

            $transactionID = $matches[1];

            $data = [
                "form_params" => [
                    "kind" => "MAIN",
                    "transaction_id" => $transactionID
                ]
            ];

            // BOOM BET
            $guzzleClient->post(env("BASE_TICKET_SUBMIT_URL") . $now, $data);


            // after successfull post request we should have this ticket bet
            $this->status = "bet";

            // after bet we take a first ticket from tickets and set it to ticket as external link
            $ticketSummary = $guzzleClient->get(env("BASE_TICKET_SUMMARY"));

            $ticketSummaryHtml = HtmlDomParser::str_get_html($ticketSummary->getBody()->getContents());
            $lastTicket = $ticketSummaryHtml->find("div[id=ticket-list]", 0)->children()[1];
            $externalTicketID = $lastTicket->getAttribute("href");

            preg_match("/ticket_id=(.*?)kind=MAIN/", $externalTicketID, $results);
          
            // by this check we can check if bet was successful, $externalTicketID will always be unique
            $externalTicketExists = UserTicket::where("external_ticket_id", $results[1])->first();
            if (!is_null($externalTicketExists)) {
                $this->status = "approved";
                $this->save();

                throw new \Exception("Ticket already exists, probably failed bet for UserTicket: " . $this->id);
            }

            $this->external_ticket_id = $results[1];

            $this->save();

            event(new UserLogEvent("Successful bet of UserTicket with ID: " . $this->id . " for game type: " . $this->ticket->game_type, $this->user->id, $this->id));

        } elseif ($bettingProviderID == BettingProvider::SECOND_PROVIDER_N) {

            // get guzzle for betting
            $guzzleClient = $user->getGuzzleForUserAndBP($this->user, $bettingProviderID, [
                "Connection" => "keep-alive",
                "Host" => "www.nike.sk",
                "Origin" => "https://www.nike.sk",
                "Referer" => "https://www.nike.sk/",
                "X-Requested-With" => "XMLHttpRequest",
            ]);

            // 1 clear basket
            $response = $guzzleClient->post(env("BASE_BET_URL_SECOND_PROVIDER"), [
                RequestOptions::JSON => [
                    "ClearBetslip", 0, new \stdClass()
                ]
            ]);
            $error = json_decode($response->getBody()->getContents())->lastCmdError;
            if ( !is_null($error)) {
                throw new \Exception("Clear basket failed with error: " . json_encode($error));
            }

            // 2 is basket empty?
            $response = $guzzleClient->post(env("BASE_BET_URL_SECOND_PROVIDER"), [
                RequestOptions::JSON => [
                    "ReadBetslips", null, new \stdClass()
                ]
            ]);
            $error = (array) json_decode($response->getBody()->__toString())->betslip->groups;
            if ( !empty($error)) {
                throw new \Exception("Basket is not empty. Cannot proceed. " . json_encode($error));
            }

            // 3 add to basket
            $basketData = new \stdClass();
            $basketData->betId = "p" . $this->ticket->match->unique_id;
            $basketData->infoNumber = $this->ticket->match->number;
            $basketData->odds = "1.94";
            $basketData->origin = "fullTextSearchSuggestions";
            $basketData->selectionCode = $this->ticket->matchbet->datainfo;

            $response = $guzzleClient->post(env("BASE_BET_URL_SECOND_PROVIDER"), [
                RequestOptions::JSON => [
                    "AddPick", 0, $basketData
                ]
            ]);
            $error = json_decode($response->getBody()->getContents())->lastCmdError;
            if ( !is_null($error)) {
                throw new \Exception("Add to basket failed with error: " . json_encode($error));
            }

            // 4 set stake
            $changeOverallMoneyStake = new \stdClass();
            $changeOverallMoneyStake->amount = BC::convertScientificNotationToString($this->bet_amount);
            $response = $guzzleClient->post(env("BASE_BET_URL_SECOND_PROVIDER"), [
                RequestOptions::JSON => [
                    "ChangeOverallMoneyStake", 0, $changeOverallMoneyStake
                ]
            ]);

            $error = json_decode($response->getBody()->getContents())->lastCmdError;
            if ( !is_null($error)) {
                throw new \Exception("Set stake failed: " . json_encode($error));
            }


            // 5 BOOM BET !!!
            $response = $guzzleClient->post(env("BASE_BET_URL_SECOND_PROVIDER"), [
                RequestOptions::JSON => [
                    "PlaceBet", 0, new \stdClass(),
                ]
            ]);
            $error = json_decode($response->getBody()->getContents())->lastCmdError;
            if ( !is_null($error)) {
                throw new \Exception("BET failed: " . json_encode($error));
            }

            if ( !is_null($error)) {
                throw new \Exception("Bet failed with error: " . json_encode($error));
            } else {

                // reset guzzle
                $guzzleClient = $user->getGuzzleForUserAndBP($this->user, $bettingProviderID, [
                    "Connection" => "keep-alive",
                    "Host" => "www.nike.sk",
                    "Origin" => "https://www.nike.sk",
                    "Referer" => "https://www.nike.sk/"
                ]);

                // 6 get security token
                $response = $guzzleClient->get(env("BASE_GET_SECURITY_TOKEN_SECOND_PROVIDER"));
                $securityToken = $response->getBody()->getContents();

                // 7 get hash of game
                $response = $guzzleClient->get(env("BASE_TICKET_SUMMARY_SECOND_PROVIDER") . $securityToken);
                $hash = json_decode($response->getBody()->getContents())->data[0]->hash;

                // by this check we can check if bet was successful, $externalTicketID will always be unique
                $externalTicketExists = UserTicket::where("external_ticket_id", $hash)->first();
                if (!is_null($externalTicketExists)) {
                    $this->status = "approved";
                    $this->save();

                    throw new \Exception("Ticket already exists, probably failed bet for UserTicket: " . $this->id);
                }

                // done
                $this->status = "bet";
                $this->external_ticket_id = $hash;

                $this->save();

                event(new UserLogEvent("Successful bet of UserTicket with ID: " . $this->id . " for game type: " . $this->ticket->game_type, $this->user->id, $this->id));
            }

        }

    }

    private function lamebet() {

        $this->status = "bet";
        $this->external_ticket_id = "LAMEBET-TICKET-LOCAL-ID";

        $this->save();
    }


    /**
     * Try to check if user ticket is already finished
     */
    public function tryToCheckResult() {

        $bettingProviderID = $this->ticket->match->betting_provider_id;

        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            $ticketData = $this->getTicketData();

            $resultClass = $ticketData->find("td[class=result-icon-cell]", 0)
                ->children[0]->getAttribute("class");

            if (strpos($resultClass, "NON_WINNING") !== false) {
                $this->loose();
                event(new UserLogEvent("UserTicket with ID: " . $this->id . " was marked as LOST.", $this->user->id, $this->id));
            } elseif (strpos($resultClass, "WINNING") !== false) {
                $this->win();
                event(new UserLogEvent("UserTicket with ID: " . $this->id . " was marked as WON.", $this->user->id, $this->id));
            }

            // try to finalize
            $this->finalize($ticketData);

        } elseif ($bettingProviderID == BettingProvider::SECOND_PROVIDER_N) {

            $ticketData = $this->getTicketData();

            if ($ticketData->status == "Lost") {
                $this->loose();
            } elseif ($ticketData->status == "Won") {
                $this->win();
            }

            // try to finalize
            $this->finalize($ticketData);

        }


    }

    /**
     * This function will try to finalize user ticket so we have correct bet_rates and amounts
     * @param null $ticketData
     */
    public function finalize($ticketData = null) {

        $bettingProviderID = $this->ticket->match->betting_provider_id;

        // if ticket data is empty, we have to provide $ticketData
        if (is_null($ticketData)) {
            $ticketData = $this->getTicketData();
        }

        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            $finalBet = $ticketData->find("tr[class=bet-type row-2]", 0)->find("td[class=value]", 0)->plaintext;

            if (!is_null($finalBet) && $this->is_finalized == 0) {

                $this->bet_rate = $finalBet;

                $this->bet_possible_win = BC::mul($this->bet_amount, $this->bet_rate, 3);
                $this->bet_possible_win = BC::roundUp($this->bet_possible_win, 2);

                $this->bet_possible_clear_win = bcsub($this->bet_possible_win, $this->bet_amount, "2");

                $this->is_finalized = 1;

                $this->save();

                event(new UserLogEvent("UserTicket with ID: " . $this->id . " was successfully finalized.", $this->user->id, $this->id));
            }

        } else {

            if ($this->is_finalized == 0) {

                $this->bet_rate = $ticketData->odds;
                $this->bet_possible_win = $ticketData->win;
                $this->bet_possible_clear_win = bcsub($this->bet_possible_win, $this->bet_amount, "2");

                $this->is_finalized = 1;
                $this->save();

                event(new UserLogEvent("UserTicket with ID: " . $this->id . " was successfully finalized.", $this->user->id, $this->id));
            }

        }

    }

    /**
     * Return ticket data from betting site
     */
    private function getTicketData() {

        $user = new \App\Services\User\User();
        $bettingProviderID = $this->ticket->match->betting_provider_id;
        $guzzleClient = $user->getGuzzleForUserAndBP($this->user, $bettingProviderID);
        $ticketLink = $this->getLinkToBettingSite($bettingProviderID);

        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            $ticketRequest = $guzzleClient->get($ticketLink);
            return HtmlDomParser::str_get_html($ticketRequest->getBody()->getContents());

        } elseif($bettingProviderID == BettingProvider::SECOND_PROVIDER_N) {

            $ticketRequest = $guzzleClient->get($ticketLink);
            return json_decode($ticketRequest->getBody()->getContents());
        }

    }

    /**
     * Mark user ticket as win :happy:
     */
    public function win() {

        $this->bet_win = 1;
        $this->status = "betanddone";

        $this->save();

        if (in_array($this->ticket->game_type, ["marcingale", "marcingale-custom"])) {
            MarcingaleUserTicket::treatBetAndDoneUserTicket($this);
        }
    }

    /**
     * Mark user ticket as lost :(
     */
    public function loose() {

        $this->bet_win = -1;
        $this->status = "betanddone";

        $this->save();

        if (in_array($this->ticket->game_type, ["marcingale", "marcingale-custom"])) {
            MarcingaleUserTicket::treatBetAndDoneUserTicket($this);
        }
    }

}
