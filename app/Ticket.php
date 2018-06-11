<?php

namespace App;

use App\Services\AppSettings;
use App\Services\User\User;
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
     * Lets bet this ticket
     */
    public function bet() {

        if ($this->status == "approved") {

            // put this somewhere else
//            AppSettings::setMaxFileSize();

            // first insert into basket
            $user = new User();
            $user->login();
            $baseURL = env("BASE_URL");
            $baseBetURL = env("BASE_BET_URL");
            $guzzleClient = $user->getUserGuzzle();

            // insert ticket into basket
            $href = $this->bet_option . "href";
            $betHref = $baseURL . $this->match->$href;
            $response = $guzzleClient->get($betHref)->getBody()->getContents();


            // reload a website to take a submit bet link
            $result = $guzzleClient->get($baseBetURL)->getBody()->getContents();

            $html = HtmlDomParser::str_get_html($response);
            // get bet link
            $betLink = $html->find("#btn-accept-ticket", 0)->getAttribute("href");

            // lets finally bet
            $bet = $guzzleClient->get($baseURL . $betLink)->getBody()->getContents();

            $this->save();
        }
    }

    /**
     * We try to create tickets from given matches
     */
    public static function tryToCreateTicketFromMatch(Match $match) {

        foreach ($match->getOptions() as $option) {

            if (is_null($match->$option)) {
                continue;
            }

            $rate = $match->$option;

            $game_type = null;
            $bet_amount = "0.5";
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

                $ticket->bet_option = $option;
                $ticket->bet_amount = $bet_amount;
                $ticket->bet_rate = $rate;
                $ticket->bet_possible_win = bcmul($ticket->bet_amount, $ticket->bet_rate, "2");
                $ticket->bet_possible_clear_win = bcsub($ticket->bet_possible_win, $bet_amount, "2");

                $ticket->match_id = $match->id;

                $ticket->save();
            }

        }

    }

}
