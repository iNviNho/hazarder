<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Ticket;
use App\UserTicket;
use BCMathExtended\BC;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UserTicketsBetValuesFix extends Command
{

    protected $signature  = "user_tickets_value_fix";
    protected $description = "Cancel tickets that could not be bet anymore";

    public function handle() {

        $userTickets = UserTicket::all();

        foreach ($userTickets as $userTicket) {

            $userTicket->bet_possible_win = BC::mul($userTicket->bet_amount, $userTicket->bet_rate, 3);
            $userTicket->bet_possible_win = BC::roundUp($userTicket->bet_possible_win, 2);

            $userTicket->bet_possible_clear_win = bcsub($userTicket->bet_possible_win, $userTicket->bet_amount, "2");

            $userTicket->save();
        }

    }

}