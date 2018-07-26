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
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketsApproveCommand extends Command
{

    protected $signature  = "tickets:approve";
    protected $description = "Approve tickets";

    public function handle() {

        $this->info("Approve tickets for all users for prepare tickets");

        $tickets = Ticket::all()
            ->where("status", "=", "prepared")
            ->where("game_type", "!=", "marcingale");

        $users = User::all()
            ->where("is_authorized", "=", "1");

        foreach ($users as $user) {
            $user->approveTickets($this, $tickets);
            $this->info("Approving tickets for user with ID: " . $user->id . " done.");
        }

    }

}