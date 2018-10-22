<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Match;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketsPrepareCommand extends Command
{

    protected $signature  = "tickets:prepare";
    protected $description = "Prepare tickets";

    public function handle() {

        $this->info("Prepare tickets from all matches that has not been played yet");

        $matches = Match::all()
            ->where('date_of_game', '>=', Carbon::now()->addMinutes(15)->format("Y-m-d H:i:s"));
        foreach ($matches as $match) {
            Ticket::tryToCreateTicketFromMatch($match, $this);
        }

    }

}