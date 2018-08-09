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

class TicketsFinalize extends Command
{

    protected $signature  = "tickets:finalize";
    protected $description = "Finalize already bet tickets so we have 100% correct values";

    public function handle() {

        $this->info("Finalize already bet tickets so we have 100% correct values");

        $users = User::all()
            ->where("is_authorized", "=", "1");

        foreach ($users as $user) {

            $userTickets = UserTicket::where(function ($q) {
                    $q->where("status", "=", "bet")
                        ->orWhere("status", "=", "betanddone");
                })
                ->where("external_ticket_id", "!=", "-")
                ->where("user_id", "=", $user->id)
                ->where("is_finalized", "=", 0); // one that was not finalized
            foreach ($userTickets->get() as $userTicket) {
                $this->info("Running finalize for UserTicket with ID: " . $userTicket->id);

                $userTicket->finalize($this);
                $rand = rand(5, 15);
                sleep($rand);
            }

        }

        $this->info("Check for check results was done.");

    }

}