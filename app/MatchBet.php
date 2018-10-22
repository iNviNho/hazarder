<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchBet extends Model
{

    protected $table = "matchbets";


    /**
     * Get the post that owns the comment.
     */
    public function match()
    {
        return $this->belongsTo('App\Match');
    }

    public function setName($rawName) {

        if (!in_array($rawName, [
            "49",
            "50",
            "51",
            "52",
            "53",
            "88",
        ])) {
            return false;
        } else {

            switch ($rawName) {
                case "49":
                    $this->name = "1";
                    break;
                case "50":
                    $this->name = "2";
                    break;
                case "51":
                    $this->name = "12";
                    break;
                case "52":
                    $this->name = "10";
                    break;
                case "53":
                    $this->name = "02";
                    break;
                case "88":
                    $this->name = "0";
                    break;
            }

            return $this;
        }

    }

}
