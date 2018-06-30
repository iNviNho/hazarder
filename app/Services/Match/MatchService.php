<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 06.06.18
 * Time: 21:27
 */

namespace App\Services\Match;

use App\Match;

class MatchService
{

    /** This method check if match already exists in DB */
    public static function alreadyExists($matchUniqueID) {

        $result = Match::where("unique_id", $matchUniqueID)->first();

        if ($result instanceof Match) {
            return true;
        }

        return false;
    }

}