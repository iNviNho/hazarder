<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Ticket;
use Illuminate\Console\Command;

class TicketsBetCommand extends Command
{

    protected $signature  = "tickets:bet";
    protected $description = "Bet tickets that are approved";

    public function handle() {

        $this->info("Start betting approved tickets");

        $tickets = Ticket::where("status", "approved")
            ->get();
        foreach ($tickets as $ticket) {
            $ticket->bet();
        }

        $this->info("Betting of " . count($tickets). " approved tickets done");
    }

}