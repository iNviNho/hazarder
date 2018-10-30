<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\MarcingaleUserRound;
use App\MarcingaleUserTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NewMarcingale extends Command
{

    protected $signature  = "newmarcingale";
    protected $description = "Prepare tickets";

    public function handle() {

        $this->info("Prepare tickets from all matches that has not been played yet");

        $marcingaleUserTickets = MarcingaleUserTicket::groupBy("round");

        foreach ($marcingaleUserTickets->get() as $marUsTicket) {

            // first we create marcingale user rounds
            $marcingaleUserRound = new MarcingaleUserRound();
            $marcingaleUserRound->id = $marUsTicket->round;
            $marcingaleUserRound->user_id = $marUsTicket->user_id;
            $marcingaleUserRound->save();

            $this->info("Marcingale user round basic created with ID " . $marcingaleUserRound->id);

        }

        foreach (MarcingaleUserTicket::all() as $marTicket) {
            $marTicket->marcingale_user_round_id = $marTicket->round;
            $marTicket->save();

            $this->info("Marcingale user round ID for marcingale user ticket set up with ID " . $marTicket->id);
        }

        $marcingaleuserRound = MarcingaleUserRound::all();

        foreach ($marcingaleuserRound as $marRound) {

            // get last ticket
            $marTicket = MarcingaleUserTicket::where([
                "marcingale_user_round_id" => $marRound->id
            ])
            ->orderBy("created_at", "DESC")
            ->first();

            // set level finished
            $marRound->level_finished = $marTicket->level;

            $status = $marTicket->status;
            if ($status == "success") {
                $marRound->status = "success";
            }
            if ($status == "failed") {
                $marRound->status = "failed";
            }
            if (in_array($status, ["needy", "bet", "canceled"])) {
                $marRound->status = "open";
            }

            // get last and use its created_at
            $marTicket = MarcingaleUserTicket::where([
                "marcingale_user_round_id" => $marRound->id
            ])
            ->orderBy("created_at", "ASC")
            ->first();

            $marRound->created_at = $marTicket->created_at;

            $marRound->save();

            $this->info("Marcingale user round updated with ID " . $marRound->id);
        }

    }

}