<?php

namespace App;

use App\Services\Ticket\TicketService;
use App\Services\User\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Sunra\PhpSimple\HtmlDomParser;

class Ticket extends Model
{

    public static $GAME_TYPES = [
        "oneten",
        "onetwenty",
        "marcingale",
        "opposite",
    ];

    /**
     * Get the phone record associated with the user.
     */
    public function match()
    {
        return $this->belongsTo('App\Match');
    }

    /**
     * Get the phone record associated with the user.
     */
    public function matchbet()
    {
        return $this->belongsTo('App\MatchBet');
    }

    /**
     * Lets bet this ticket
     */
    public function bet() {

        // first insert into basket
        $userEntity = \App\User::get();
        $user = new User($userEntity);
        $user->login();

        $now = Carbon::now()->getTimestamp();
        $guzzleClient = $user->getUserGuzzle();

        // clear ticket & have fresh one
        $clearTicket = $guzzleClient->get(env("BASE_TICKET_CLEAR_URL") . $now);

        // add to basket
        $guzzleClient->get(env("BASE_BET_URL") . $this->matchbet->datainfo . "&tip_id=" . $this->matchbet->dataodd . "&value=" . trim($this->matchbet->value) . "&kind=MAIN&_ts=" . $now);

        // here we can do check if we have 1 number in #fixed-ticket-link span.value -> plaintext
        $result = $guzzleClient->get(env("BASE_TODAY_GROUPS_URL"));

        // get ticket
        $tiket = $guzzleClient->get(env("BASE_TICKET_URL") . $now);

        $tiketresponse = $tiket->getBody()->getContents();

        $tiketresponse = str_replace("\\n", "", $tiketresponse);
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

    /**
     * We try to create tickets from given matches
     * @param Match $match
     * @param Command $command
     */
    public static function tryToCreateTicketFromMatch(Match $match, Command $command) {

        $betOppositeMatchBetID = false;
        foreach ($match->getMatchBets()->get() as $matchBet) {

            $rate = trim($matchBet->value);

            $game_type = null;
            // type of onetype
            // if $rate <= 1.10
            if (bccomp($rate, "1.11", 2) == -1) {
                $game_type = "oneten";
                $betOppositeMatchBetID = $matchBet->id;
            }

            // type of twotwenty
            // if $rate > 1.1 && $rate <= 1.20
            if (bccomp($rate, "1.1", 2) == 1 && bccomp($rate, "1.21", 2) == -1) {
                $game_type = "onetwenty";
            }

            // type of marcingale
            // if $rate >= 1.9 && $rate <= 2.11
            if (bccomp($rate, "1.89", 2) == 1 && bccomp($rate, "2.11", 2) == -1) {
                $game_type = "marcingale";
            }

            // we have a game type! Lets create ticket from it!
            if (!is_null($game_type)) {
                $ticket = self::createAndInsertTicket($match->id, $matchBet->id, "prepared", "tobeplayed", $game_type);
                if (!$ticket) {
                    return;
                }
                $command->info("Created ticket " . $ticket->id . " of type " . $ticket->game_type);
            }

        }

        // we should bet on opposite
        if ($betOppositeMatchBetID != false) {

            // we take
            if ($match->type == "normal" || $match->type == "goldengame" || $match->type == "simple") {

                $ticket = self::createAndInsertTicket($match->id, $betOppositeMatchBetID, "prepared", "tobeplayed", "opposite");
                if (!$ticket) {
                    return;
                }
                $command->info("Created ticket " . $ticket->id . " of type " . $ticket->game_type);
            }
        }

    }

    /**
     * Just create and insert ticket
     * Returns false, if match and its matchbet was already bet
     * @param $matchID
     * @param $matchBetID
     * @param $status
     * @param $result
     * @param $gameType
     * @return Ticket|bool
     */
    private static function createAndInsertTicket($matchID, $matchBetID, $status, $result, $gameType) {

        $ticket = new Ticket();

        $ticket->match_id = $matchID;
        $ticket->matchbet_id = $matchBetID;

        // do we already have ticket for this game?
        if (TicketService::ticketForMatchAlreadyExists($matchID)) {

            // is it the same one?
            if (TicketService::ticketForMatchAndMatchBetAlreadyExists($matchID, $matchBetID)) {
                // we dont want the same one
                return false;
            }

            // so far we allow creating another ticket for different match bet
        }
        $ticket->status = $status;
        $ticket->result = $result;

        $ticket->game_type = $gameType;

        $ticket->save();

        return $ticket;
    }


    public static function tryToCheckResult(Ticket $ticket) {

        // first insert into basket
        $user = new User();
        $user->login();

        $url = env("BASE_TICKET_SHOW") . $ticket->external_ticket_id . "&kind=MAIN";

        $ticketRequest = $user->getUserGuzzle()->get($url);

        $ticketHTML = HtmlDomParser::str_get_html($ticketRequest->getBody()->getContents());

        $resultClass = $ticketHTML->find("td[class=result-icon-cell]", 0)
                    ->children[0]->getAttribute("class");

        if (strpos($resultClass, "NON_WINNING") !== false) {
            $ticket->loose();
        } elseif (strpos($resultClass, "WINNING") !== false) {
            $ticket->win();
        } else {
            // not result yet
        }

    }

    private function win() {

        $this->bet_win = 1;
        $this->status = "done";

        $this->save();
    }

    private function loose() {

        $this->bet_win = -1;
        $this->status = "done";

        $this->save();
    }

}
