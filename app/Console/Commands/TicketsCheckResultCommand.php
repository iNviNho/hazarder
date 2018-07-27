<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\User;
use App\UserTicket;
use Illuminate\Console\Command;

class TicketsCheckResultCommand extends Command
{

    protected $signature  = "tickets:checkresult";
    protected $description = "Check results of all bet tickets";

    public function handle() {

        $this->info("Check latest tickets");

        $users = User::all()
            ->where("is_authorized", "=", "1");

        foreach ($users as $user) {

            $userTickets = UserTicket::all()
                ->where("status", "=", "bet")
                ->where("user_id", "=", $user->id);
            foreach ($userTickets as $userTicket) {
                $userTicket->tryToCheckResult($this);
                $this->info("Checking result of UserTicket with ID: " . $userTicket->id . " successfully ran.");

            }

            $user->updateCredit($this);
        }

        $this->info("Check for check results was done.");

    }

}