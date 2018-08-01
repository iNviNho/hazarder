<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 06.06.18
 * Time: 21:27
 */

namespace App\Services\Ticket;

use App\Ticket;

class TicketService
{

    /** This method check if match already exists in DB */
    public static function ticketForMatchAlreadyExists($matchID) {

        $result = Ticket::where("match_id", "=", $matchID)->first();

        if ($result instanceof Ticket) {
            return true;
        }

        return false;
    }

    /** This method check if match already exists in DB */
    public static function ticketForMatchAndMatchBetAlreadyExists($matchID, $matchBetID) {

        $result = Ticket::where("match_id", "=", $matchID)
            ->where("matchbet_id", "=", $matchBetID)->first();

        if ($result instanceof Ticket) {
            return true;
        }

        return false;
    }

}