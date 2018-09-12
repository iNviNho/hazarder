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

    public function marcingaleUserRound()
    {
        return $this->belongsTo('App\MarcingaleUserRound');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * This function just returns true if new marcingale ticket round should started
     * Or returns marcingale ticket round that should follow
     * @param $user
     * @return boolean
     * @return MarcingaleUserRound
     * @throws Exception
     */
    public static function shouldWeCreateNewMarcingaleTicketRound($user) {

        // lets get every marcingale user round that is not finished = OPEN
        $marcingaleUserRounds = MarcingaleUserRound::where([
            "user_id" => $user->id,
            "status" => "open"
        ])
        ->orderBy("created_at", "ASC")
        ->get();


        // lets loop through all
        foreach ($marcingaleUserRounds as $marUserRound) {

            // now we have to get the oldest one user ticket
            $marcingaleUserTicket = MarcingaleUserTicket::where([
                    "marcingale_user_round_id" => $marUserRound->id
            ])
            ->orderBy("created_at", "DESC")
            ->first();

            // and lets find out if by any chance was not user ticket either:
            // a) canceled by some error so we have to continue with the marcingale
            // b) or it was bet and done and since we have only open rounds, this must be failed
            $status = $marcingaleUserTicket->userTicket()->first()->status;
            if ( in_array($status, ["canceled", "betanddone"]) ) {
                if ($marcingaleUserTicket->userTicket()->first()->bet_win < 1) {
                    return $marUserRound;
                }
                throw new \Exception("This should never happen" . json_encode($marcingaleUserTicket));
            }

        }

        return true;
    }

    public static function createFreshMarcingaleUserTicketRound($user) {

        $marcingaleUserRound = new MarcingaleUserRound();
        $marcingaleUserRound->user_id = $user->id;
        $marcingaleUserRound->level_finished = 1;
        $marcingaleUserRound->status = "open";

        $marcingaleUserRound->save();

        $marcingaleUserTicket = new MarcingaleUserTicket();
        $marcingaleUserTicket->user_id = $user->id;
        $marcingaleUserTicket->level = 1;
        $marcingaleUserTicket->marcingale_user_round_id = $marcingaleUserRound->id;

        return $marcingaleUserTicket;
    }

    public static function createContinuousMarcingaleUserTicketRound($user, MarcingaleUserRound $marcingaleTicketRound) {

        $doneLevels = 0;
        foreach ($marcingaleTicketRound->getMarcingaleUserTickets()->get() as $marTicket) {
            if ($marTicket->userTicket()->first()->status != "canceled") {
                $doneLevels++;
            }
        }

        $marcingaleUserTicket = new MarcingaleUserTicket();
        $marcingaleUserTicket->user_id = $user->id;
        $marcingaleUserTicket->level = $doneLevels + 1;
        $marcingaleUserTicket->marcingale_user_round_id = $marcingaleTicketRound->id;

        return $marcingaleUserTicket;
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

            $marcingaleUserRound = $marcingaleUserTicket->marcingaleUserRound()->first();

            $marcingaleUserRound->status = "success";
            $marcingaleUserRound->level_finished = $marcingaleUserTicket->level;

        } elseif ($userTicket->bet_win == -1) {

            $marcingaleUserRound = $marcingaleUserTicket->marcingaleUserRound()->first();

            $user = User::where("id", "=", $userTicket->user_id)->first();
            $maxMarcingaleLevel = trim($user->getSettings()->first()->max_marcingale_level);

            if ($marcingaleUserRound->level_finished >= $maxMarcingaleLevel) {
                $marcingaleUserRound->status = "failed";
            } else {
                $marcingaleUserRound->status = "open";
                $marcingaleUserRound->level_finished = $marcingaleUserTicket->level + 1;
            }

        }

        $marcingaleUserRound->save();
    }

}
