<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Events\UserLogEvent;
use App\MarcingaleUserTicket;
use App\Ticket;
use App\UserLog;
use App\UserTicket;
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
        ->where("status", "!=", "canceled")
        ->get();

        // cancel tickets that were not bet and the game has already started
        foreach ($tickets as $ticket) {
            $ticket->status = "canceled";
            $ticket->save();

            $this->info("Ticket with ID: " . $ticket->id . " was canceled because the game has already started.");
        }

        // cancel user tickets that were approved but failed to be bet
        $userTickets = UserTicket::where("status", "=", "approved")
            ->where("created_at", "<=", Carbon::now()->subHour(1)->format("Y-m-d H:i:s"));
        foreach ($userTickets->get() as $userTicket) {
            $userTicket->status = "canceled";
            $userTicket->save();

            event(new UserLogEvent("UserTicket with ID: ". $userTicket->id . " was canceled because probably betting was not successful.", $userTicket->user_id, $userTicket->id));
        }

        //
        $marcingaleUserTickets = MarcingaleUserTicket::where([
            "status" => "bet"
        ])->get();
        foreach ($marcingaleUserTickets as $marUserTicket) {
            if ($marUserTicket->userTicket()->first()->status == "canceled") {
                $marUserTicket->status = "canceled";
                $marUserTicket->save();
            }
        }

    }

}