<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
     * This function just returns true if new marcingale user round should start
     * Or returns marcingale user round that should be continued
     * @param $user
     * @param $bettingProviderID
     * @return bool
     * @throws \Exception
     */
    public static function shouldWeCreateNewMarcingaleUserRound($user, $bettingProviderID) {

        // lets get every marcingale user round that is not finished = OPEN
        $marcingaleUserRounds = MarcingaleUserRound::where([
            "user_id" => $user->id,
            "status" => "open",
            "betting_provider_id" => $bettingProviderID,
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

            // if marcingale user round does not have any marcingale user ticket
            // lets continue with this marcingale user round
            if (is_null($marcingaleUserTicket)) {
                return $marUserRound;
            }

            // and lets find out if by any chance was not user ticket either:
            // a) canceled by some error so we have to continue with the marcingale
            // b) or it was bet and done and since we have only open rounds, this must be failed
            $status = $marcingaleUserTicket->userTicket->status;
            if ( in_array($status, ["canceled", "betanddone"]) ) {
                if ($marcingaleUserTicket->userTicket->bet_win < 1) {
                    return $marUserRound;
                }
                throw new \Exception("This should never happen" . json_encode($marcingaleUserTicket));
            }

        }

        return true;
    }

    /**
     * This function creates fresh marcingale user round
     * and creates its first marcingaleUserTicket
     * @param $user
     * @param $bettingProviderID
     * @return MarcingaleUserTicket
     */
    public static function createFreshMarcingaleUserRound($user, $bettingProviderID) {

        $marcingaleUserRound = new MarcingaleUserRound();
        $marcingaleUserRound->user_id = $user->id;
        $marcingaleUserRound->level_finished = 1;
        $marcingaleUserRound->status = "open";
        $marcingaleUserRound->betting_provider_id = $bettingProviderID;

        $marcingaleUserRound->save();

        $marcingaleUserTicket = new MarcingaleUserTicket();
        $marcingaleUserTicket->user_id = $user->id;
        $marcingaleUserTicket->level = 1;
        $marcingaleUserTicket->marcingale_user_round_id = $marcingaleUserRound->id;

        return $marcingaleUserTicket;
    }

    public static function createContinuousMarcingaleUserTicket($user, MarcingaleUserRound $marcingaleTicketRound) {

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

    public static function getBetAmountForContinuousMarcingaleUserTicket(MarcingaleUserRound $marRound, $level, $bettingProviderID) {

        $startedAmountOfThisRound = $marRound->getMarcingaleUserTickets()->get()->last();
      
        // did we even started?
        if (is_null($startedAmountOfThisRound)) {
            // if not, return just bet_amount from settings
            return $marRound->user->getSettings($bettingProviderID)->bet_amount;
        }

        $betAmount = $startedAmountOfThisRound->userTicket->bet_amount;
        for ($i = 1; $i < $level; $i++) {
            $betAmount = bcmul($betAmount, 2, 2);
        }

        return $betAmount;
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

            if ($marcingaleUserTicket->level >= $maxMarcingaleLevel || $userTicket->ticket->game_type == "marcingale-custom") {
                $marcingaleUserRound->status = "failed";
            } else {
                $marcingaleUserRound->status = "open";
                $marcingaleUserRound->level_finished = $marcingaleUserTicket->level + 1;
            }

        }

        $marcingaleUserRound->save();
    }

}
