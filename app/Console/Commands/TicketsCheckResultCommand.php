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
use Carbon\Carbon;
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

            $userTickets = UserTicket::where("status", "=", "bet")
                ->where("user_id", "=", $user->id)
                ->whereHas("ticket", function($query) {
                    $query->whereHas("match", function($query) {
                        // check result only after 2 and half hours after game has started
                        $query->where("date_of_game", ">=", Carbon::now()->addMinutes(150)->format("Y-m-d H:i:s"));
                    });
                });

            foreach ($userTickets->get() as $userTicket) {
                $userTicket->tryToCheckResult($this);

                sleep(rand(5, 15));
            }

            $user->updateCredit();
        }

        $this->info("Check for check results was done.");

    }

}