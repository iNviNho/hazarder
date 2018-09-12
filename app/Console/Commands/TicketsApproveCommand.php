<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Ticket;
use App\User;
use App\UserTicket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketsApproveCommand extends Command
{

    protected $signature  = "tickets:approve";
    protected $description = "Approve tickets";

    public function handle() {

//        UserTicket::all()->where("id", 835)->first()->loose();
//        die();

        $this->info("Approve tickets for all users from prepared tickets");

        $tickets = Ticket::select(["tickets.*", "matches.date_of_game"])
            ->join('matches', 'matches.id', '=', 'tickets.match_id')
            ->where("status", "=", "prepared")
            ->where('date_of_game', '>=', Carbon::now()->addMinutes(15)->format("Y-m-d H:i:s"))
            ->orderBy("date_of_game", "asc");

        $users = User::all()
            ->where("is_authorized", "=", "1");

        foreach ($users as $user) {
            $user->approveTickets($tickets);
        }

    }

}