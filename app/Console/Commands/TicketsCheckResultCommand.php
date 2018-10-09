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
                ->where("user_id", "=", $user->id);

            foreach ($userTickets->get() as $userTicket) {

                // check user tickets which match was played 2.5 hours ago
                $nowTimestamp = Carbon::now()->getTimestamp();
                $matchFinishTime = Carbon::createFromTimeString($userTicket->ticket->match->date_of_game)->addMinutes(150)->getTimestamp();

                if ($nowTimestamp > $matchFinishTime) {
                    $userTicket->tryToCheckResult($this);

                    sleep(rand(5, 15));
                }

            }

            $user->updateCredit();
        }

        $this->info("Check for check results was done.");
    }

}