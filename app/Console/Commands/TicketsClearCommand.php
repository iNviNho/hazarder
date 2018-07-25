<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketsClearCommand extends Command
{

    protected $signature  = "tickets:clear";
    protected $description = "Cancel tickets that could not be bet anymore";

    public function handle() {

        $this->info("We cancel tickets that were not bet and the game has already started");

        $tickets = Ticket::whereHas('match', function ($q) {
            $q->where('date_of_game', '<=', Carbon::now()->format("Y-m-d H:i:s"));
        })
        ->where("status", "!=", "bet")
        ->where("status", "!=", "disapproved")
        ->where("status", "!=", "canceled")
        ->get();

        foreach ($tickets as $ticket) {
            $ticket->status = "canceled";
            $ticket->save();

            $this->info("Ticket with ID: " . $ticket->id . " was canceled because the game was already played and ticket not bet.");
        }

    }

}