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
use Illuminate\Console\Command;

class TicketsCheckResult extends Command
{

    protected $signature  = "tickets:checkresult";
    protected $description = "Check results of all bet tickets";

    public function handle() {

        $this->info("Check latest tickets");

        /** @var */
        $tickets = Ticket::where("status", "bet")
            ->get();
        foreach ($tickets as $ticket) {
            Ticket::tryToCheckResult($ticket);
        }

        $this->info("Check for " . count($tickets) . " was done.");
    }

}