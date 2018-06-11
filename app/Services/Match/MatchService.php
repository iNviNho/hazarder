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

    public function insertGames($matches = []) {

        /** @var Match $match */
        foreach ($matches as $match) {
            $match->prepareBeforeInsert();

            $match->save();
        }

    }

}