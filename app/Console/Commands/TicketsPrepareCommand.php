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

class TicketsPrepareCommand extends Command
{

    protected $signature  = "tickets:prepare";
    protected $description = "Prepare tickets";

    public function handle() {

        $this->info("Prepare tickets from todays matches");

        /** @var Think about that matches should not be duplicated $matches */
        $matches = Match::all();
        foreach ($matches as $match) {
            Ticket::tryToCreateTicketFromMatch($match);
        }

    }

}