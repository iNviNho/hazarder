<?php

namespace App;

use BCMathExtended\BC;
use Illuminate\Database\Eloquent\Model;

class MarcingaleUserRound extends Model
{

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get user
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }


    /**
     * Get the comments for the blog post.
     */
    public function getMarcingaleUserTickets()
    {
        return $this->hasMany('App\MarcingaleUserTicket')->orderBy("created_at", "DESC");
    }

    public function getProfit() {

        $result = 0;
        foreach ($this->getMarcingaleUserTickets()->get() as $marUserTicket) {

            $userTicket = $marUserTicket->userTicket()->first();

            // it had to be bet
            if ($userTicket->status == "betanddone") {
                // first substract what you bet
                $result = BC::sub($result, $userTicket->bet_amount, 2);
                // and only if we win, we get money
                if ($userTicket->bet_win == 1) {
                    $result = BC::add($result, $userTicket->bet_possible_win, 2);
                }
            }

        }

        return $result;
    }

}
