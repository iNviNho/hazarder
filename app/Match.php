<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{

    private $options = [
        "a", "b", "c", "d", "e"
    ];

    public function prepareBeforeInsert() {

        /**
         * Perform last check before we proceed to database insertion
         */
        try {

            /* match type default */
            $this->type = "normal";

            $this->category = trim(str_replace("\t", "", $this->category));

            $this->name = trim($this->name);
            $teams = explode("-", $this->name);
            if (count($teams) > 1) {
                $this->teama = trim($teams[0]);
                $this->teamb = trim($teams[1]);
            }

            if (is_null($this->c)) {
                $this->type = "goldengame";

                $this->c = $this->b;
                $this->b = null;
            }
            if (is_null($this->b) && is_null($this->c)) {
                $this->type = "single";
            }

            $today = new Carbon();
            $this->created_at = $today;

            $date = explode("\r\n", $this->date_of_game);
            $dateOfGame = trim(str_replace("\t", "", $date[0])) . $today->year;
            $timeOfGame = trim(str_replace("\t", "", $date[1]));

            $datetimeOfGame = $dateOfGame . " " . $timeOfGame;
            $this->date_of_game = Carbon::createFromTimeString($datetimeOfGame);

            $this->unique_name = $this->name . ":" . $this->date_of_game->getTimestamp();

        } catch (\Throwable $e) {

            // decide what to do with the fucked ones
            dd($this->name);
            dd($this);
        }

    }

    public function getOptions() {
        return $this->options;
    }

}
