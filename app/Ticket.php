<?php

namespace App;

use App\Services\Ticket\TicketService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{

    public static $GAME_TYPES = [
        "marcingale",
        "oneten",
        "onetwenty",
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
     * We try to create tickets from given matches
     * @param Match $match
     * @param Command $command
     */
    public static function tryToCreateTicketFromMatch(Match $match, Command $command) {

        foreach ($match->getMatchBets()->get() as $matchBet) {

            $rate = trim($matchBet->value);

            $game_type = null;
            // type of onetype
            // if $rate <= 1.10
            if ( (bccomp($rate, "1.11", 2) == -1) && ($rate != "")) {
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

                // for marcingale check our custom logic
                if ($game_type == "marcingale") {
                    // if it is not valid, do not proceed
                    if (!self::isValidMarcingale($match, $matchBet)) {
                        continue;
                    }
                }


                $ticket = self::createAndInsertTicket($match, $matchBet, "prepared", "tobeplayed", $game_type);
                if (!$ticket) {
                    continue;
                }
                $command->info("Created ticket " . $ticket->id . " of type " . $ticket->game_type . " with rate: " . $rate);
            }

        }

    }

    private static function isValidMarcingale($match, $matchBet) {

        // we only support goldengame|normal for marcingale
        if (in_array(trim($match->type), ["goldengame", "normal"])) {

            // for golden game, we simply have to be favorit
            if ($match->type == "goldengame") {

                $otherBet = null;
                foreach ($match->getMatchBets()->get() as $matchBetForeach) {
                    if ($matchBetForeach->id != $matchBet->id) {
                        $otherBet = $matchBetForeach;
                    }
                }

                // lets finally check if we are favorits
                if (bccomp($matchBet->value, $otherBet->value, 2) < 0) {
                    return true;
                } else {
                    return false;
                }

            }

            // for normal
            if ($match->type == "normal") {

                // we take only 1, 2 name bets
                // 0, 10 or 02 will never be favorites in marcingale
                if (in_array(trim($matchBet->name), ["1", "2"])) {
                    // wow, we here
                    // lets check if we are favorits
                    $oppositeName = "1";
                    if (trim($matchBet->name) == "1") {
                        $oppositeName = "2";
                    }

                    $otherBet = MatchBet::where("match_id", "=", $match->id)
                        ->where("name", "=", $oppositeName)
                        ->first();

                    // lets finally check if we are favorits
                    if (bccomp($matchBet->value, $otherBet->value, 2) < 0) {
                        return true;
                    } else {
                        return false;
                    }

                } else {
                    // this should never come here
                    return false;
                }

            }

            // nothing returned true? then return false
            return false;

        } else {
            return false;
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
    public static function createAndInsertTicket($match, $matchBet, $status, $result, $gameType, $byPassChecks = false) {

        $ticket = new Ticket();

        $ticket->match_id = $match->id;
        if ($matchBet instanceof MatchBet) {
            $ticket->matchbet_id = $matchBet->id;
        } else {
            $ticket->matchbet_id = $matchBet;
        }

        // do we already have ticket for this game?
        if (TicketService::ticketForMatchAlreadyExists($match->id) && !$byPassChecks) {

            // is it the same one?
            if (TicketService::ticketForMatchAndMatchBetAlreadyExists($match->id, $ticket->matchbet_id)) {
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

}
