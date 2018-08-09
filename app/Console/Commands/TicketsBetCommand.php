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

        $this->info("Start betting approved tickets");

        $users = User::all()
            ->where("is_authorized", "=", "1");

        foreach ($users as $user) {

            $tickets = UserTicket::all()
                ->where("status", "=", "approved")
                ->where("user_id", "=", $user->id);
            foreach ($tickets as $userTicket) {
                $userTicket->bet($this);
                $this->info("Betting of UserTicket with ID: " . $userTicket->id . " successfully done.");
                $random = rand(10,20);
                sleep($random);
            }
            sleep(10);
            $user->updateCredit($this);
        }
    }

}