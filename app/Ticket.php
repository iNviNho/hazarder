<?php

namespace App;

use App\Services\AppSettings;
use App\Services\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sunra\PhpSimple\HtmlDomParser;

class Ticket extends Model
{

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
        $user = new User();
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
     */
    public static function tryToCreateTicketFromMatch(Match $match) {

        foreach ($match->getMatchBets()->get() as $matchBet) {

            $rate = trim($matchBet->value);

            $game_type = null;
            // type of onetype
            // if $rate <= 1.10
            if (bccomp($rate, "1.11", 2) == -1) {
                $game_type = "oneten";
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

                $ticket = new Ticket();

                $ticket->status = "prepared";
                $ticket->result = "tobeplayed";

                $ticket->game_type = $game_type;

                $ticket->bet_option = $matchBet->name;
                // we will alter maybe one day
                $ticket->bet_amount = "0.5";
                $ticket->bet_rate = $rate;
                $ticket->bet_possible_win = bcmul($ticket->bet_amount, $ticket->bet_rate, "2");
                $ticket->bet_possible_clear_win = bcsub($ticket->bet_possible_win, $ticket->bet_amount, "2");

                $ticket->match_id = $match->id;
                $ticket->matchbet_id = $matchBet->id;

                $ticket->save();
            }

        }

    }

}
