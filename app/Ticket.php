<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{

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
