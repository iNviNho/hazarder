<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarcingaleUserTicket extends Model
{

    protected $table = "marcingale_user_tickets";

    public function userTicket()
    {
        return $this->belongsTo('App\UserTicket');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * This function just returns false if new round should start
     * @param $user
     * @return boolean
     */
    public static function shouldWeCreateNewMarcingaleTicketRound($user) {

        // can we continue any new MarcingaleTicket already?
        // wtf magic
        $magic = DB::table(DB::raw('marcingale_user_tickets as s1'))
            ->join(
                DB::raw('(SELECT round, MAX(level) AS level FROM marcingale_user_tickets GROUP BY round) as s2'),
                function($query) {
                    $query->on('s1.round', '=', 's2.round')
                        ->on('s1.level', '=', 's2.level');
                })
            ->where("status", "=", "needy")
            ->where("user_id","=", $user->id)
            ->count();

        if ($magic == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function createFreshMarcingaleUserTicketRound($user) {

        $marcingaleUserTicket = new MarcingaleUserTicket();
        $marcingaleUserTicket->user_id = $user->id;
        $marcingaleUserTicket->level = 1;
        $marcingaleUserTicket->round = self::getNewRoundID();
        $marcingaleUserTicket->status = "bet";

        return $marcingaleUserTicket;
    }

    public static function createContinuousMarcingaleUserTicketRound($user) {

        $needyMarcingaleUserTicket = DB::table(DB::raw('marcingale_user_tickets as s1'))
            ->join(
                DB::raw('(SELECT round, MAX(level) AS level FROM marcingale_user_tickets GROUP BY round) as s2'),
                function($query) {
                    $query->on('s1.round', '=', 's2.round')
                        ->on('s1.level', '=', 's2.level');
                })
            ->where("status", "=", "needy")
            ->where("user_id","=", $user->id)
            ->first();

        $marcingaleUserTicket = new MarcingaleUserTicket();
        $marcingaleUserTicket->user_id = $user->id;
        $marcingaleUserTicket->level = $needyMarcingaleUserTicket->level + 1;
        $marcingaleUserTicket->round = $needyMarcingaleUserTicket->round;
        $marcingaleUserTicket->status = "bet";

        return $marcingaleUserTicket;
    }

    private static function getNewRoundID() {

        $maxRound = MarcingaleUserTicket::max("round");

        if (is_null($maxRound)) {
            return 1;
        } else {
            $maxRound += 1;
            return $maxRound;
        }
    }

    public static function getBetAmountForContinuousUserTicket($betAmount, $level) {

        $previousBet = $betAmount;
        for ($i = 2; $i <= $level; $i++) {
            $previousBet = bcmul($previousBet, 2, 2);
        }

        return $previousBet;
    }

    public static function treatBetAndDoneUserTicket($userTicket) {

        $marcingaleUserTicket = MarcingaleUserTicket::where("user_ticket_id", "=", $userTicket->id)->first();

        if ($userTicket->bet_win == 1) {
            $marcingaleUserTicket->status = "success";
        } elseif ($userTicket->bet_win == -1) {

            $user = User::where("id", "=", $userTicket->user_id)->first();
            $maxMarcingaleLevel = $user->getSettings()->first()->max_marcingale_level;

            if ($marcingaleUserTicket->level >= trim($maxMarcingaleLevel)) {
                $marcingaleUserTicket->status = "failed";
            } else {
                $marcingaleUserTicket->status = "needy";
            }
        }

        $marcingaleUserTicket->save();
    }

}
