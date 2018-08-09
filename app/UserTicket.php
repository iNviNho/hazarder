<?php

namespace App;

use BCMathExtended\BC;
use Carbon\Carbon;
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
     * Lets bet this ticket
     */
    public function bet($command) {

        if (env("APP_ENV") == "local") {
            $this->lamebet();
            return;
        }

        // first insert into basket
        $user = new \App\Services\User\User($this->user);
        if (!$user->login()) {
            $command->info("User with ID: " . $this->user->id . " was not successfully logged in :( RIP");
            return;
        }

        $now = Carbon::now()->getTimestamp();
        $guzzleClient = $user->getUserGuzzle();

        // clear ticket & have fresh one
        $guzzleClient->get(env("BASE_TICKET_CLEAR_URL") . $now);

        // add to basket
        $guzzleClient->get(env("BASE_BET_URL") . $this->ticket->matchbet->datainfo . "&tip_id=" . $this->ticket->matchbet->dataodd . "&value=" . trim($this->bet_rate) . "&kind=MAIN&_ts=" . $now);

        // here we can do check if we have 1 number in #fixed-ticket-link span.value -> plaintext
//        $result = $guzzleClient->get(env("BASE_TODAY_GROUPS_URL"));

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

        $this->status = "bet";

        // after bet we take a first ticket from tickets and set it to ticket as external link
        $ticketSummary = $guzzleClient->get(env("BASE_TICKET_SUMMARY"));

        $ticketSummaryHtml = HtmlDomParser::str_get_html($ticketSummary->getBody()->getContents());
        $lastTicket = $ticketSummaryHtml->find("div[id=ticket-list]", 0)->children()[1];
        $externalTicketID = $lastTicket->getAttribute("href");

        preg_match("/ticket_id=(.*?)kind=MAIN/", $externalTicketID, $results);

        $this->external_ticket_id = $results[1];

        $this->save();
    }

    private function lamebet() {

        $this->status = "bet";
        $this->external_ticket_id = "LAMEBET-TICKET-LOCAL-ID";

        $this->save();
    }


    public function tryToCheckResult($command) {

        $user = new \App\Services\User\User($this->user);
        if (!$user->login()) {
            $command->info("User with ID: " . $this->user->id . " was not successfully logged in :( RIP");
            return;
        }

        $url = env("BASE_TICKET_SHOW") . $this->external_ticket_id . "&kind=MAIN";

        $ticketRequest = $user->getUserGuzzle()->get($url);

        $ticketHTML = HtmlDomParser::str_get_html($ticketRequest->getBody()->getContents());

        $resultClass = $ticketHTML->find("td[class=result-icon-cell]", 0)
            ->children[0]->getAttribute("class");

        if (strpos($resultClass, "NON_WINNING") !== false) {
            $this->loose();
        } elseif (strpos($resultClass, "WINNING") !== false) {
            $this->win();
        } else {
            // not result yet
        }


        // lets also try to finalize
        $finalBet = $ticketHTML->find("tr[class=bet-type row-2]", 0)->find("td[class=value]", 0)->plaintext;

        if (!is_null($finalBet)) {

            $this->bet_rate = $finalBet;

            $this->bet_possible_win = BC::mul($this->bet_amount, $this->bet_rate, 3);
            $this->bet_possible_win = BC::roundUp($this->bet_possible_win, 2);

            $this->bet_possible_clear_win = bcsub($this->bet_possible_win, $this->bet_amount, "2");

            $this->is_finalized = 1;

            $this->save();

            $command->info("UserTicket with ID: ". $this->id . " was successfully finalized");
        }


    }

    public function finalize($command) {

        $user = new \App\Services\User\User($this->user);
        if (!$user->login()) {
            $command->info("User with ID: " . $this->user->id . " was not successfully logged in :( RIP");
            return;
        }

        $url = env("BASE_TICKET_SHOW") . $this->external_ticket_id . "&kind=MAIN";

        $ticketRequest = $user->getUserGuzzle()->get($url);

        $ticketHTML = HtmlDomParser::str_get_html($ticketRequest->getBody()->getContents());

        $finalBet = $ticketHTML->find("tr[class=bet-type row-2]", 0)->find("td[class=value]", 0)->plaintext;

        if (!is_null($finalBet)) {

            $this->bet_rate = $finalBet;

            $this->bet_possible_win = BC::mul($this->bet_amount, $this->bet_rate, 3);
            $this->bet_possible_win = BC::roundUp($this->bet_possible_win, 2);

            $this->bet_possible_clear_win = bcsub($this->bet_possible_win, $this->bet_amount, "2");

            $this->is_finalized = 1;

            $this->save();

            $command->info("UserTicket with ID: ". $this->id . " was successfully finalized");
        }
    }

    private function win() {

        $this->bet_win = 1;
        $this->status = "betanddone";

        $this->save();

        if ($this->ticket->game_type == "marcingale") {
            MarcingaleUserTicket::treatBetAndDoneUserTicket($this);
        }
    }

    private function loose() {

        $this->bet_win = -1;
        $this->status = "betanddone";

        $this->save();

        if ($this->ticket->game_type == "marcingale") {
            MarcingaleUserTicket::treatBetAndDoneUserTicket($this);
        }
    }

}
