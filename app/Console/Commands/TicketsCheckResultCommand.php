<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Events\UserLogEvent;
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

                sleep(rand(5, 15));
            }

            $user->updateCredit();
        }

        $this->info("Check for check results was done.");

    }

}