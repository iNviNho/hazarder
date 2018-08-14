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

class TicketsBetCommand extends Command
{

    protected $signature  = "tickets:bet";
    protected $description = "Bet tickets that are approved";

    public function handle() {

        $this->info("Start betting prepared AND approved tickets");

        // for all authorized users
        $users = User::all()
            ->where("is_authorized", "=", "1");

        foreach ($users as $user) {

            // get user tickets
            $tickets = UserTicket::all()
                ->where("status", "=", "approved")
                ->where("user_id", "=", $user->id);

            // approve
            foreach ($tickets as $userTicket) {
                $userTicket->bet($this);
                sleep(rand(10,20));
            }

            // in the end update credit
            $user->updateCredit();
        }
    }

}