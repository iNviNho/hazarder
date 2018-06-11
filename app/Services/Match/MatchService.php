<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 06.06.18
 * Time: 21:27
 */

namespace App\Services\Match;


use App\Model\Entity\MatchOld;
use Cake\ORM\TableRegistry;

class MatchService
{

    public function insertGames($matches = []) {

        /** @var MatchOld $match */
        foreach ($matches as $match) {
            $match->prepareBeforeInsert();

            TableRegistry::get("Matches")->save($match);
        }

    }

}