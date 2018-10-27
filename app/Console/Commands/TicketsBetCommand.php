<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\BettingProvider;
use App\Settings;
use App\User;
use App\UserTicket;
use Illuminate\Console\Command;

class TicketsBetCommand extends Command
{

    protected $signature  = "tickets:bet";
    protected $description = "Bet tickets that are approved";

    public function handle() {

        $this->info("Start betting approved tickets");

        $bettingProviders = BettingProvider::all();
        foreach ($bettingProviders as $bP) {

            // is betting provider enabled
            if (BettingProvider::isEnabled($bP->id)) {
                continue;
            }

            // for all authorized users
            $users = User::all()
                ->where("is_authorized", "=", "1");
            foreach ($users as $user) {

                // check if this user has this betting provider active
                if (!Settings::isBettingProviderEnabled($user->id, $bP->id)) {
                    continue;
                }

                // get user tickets for this BP
                $userTickets = UserTicket::where("status", "=", "approved")
                    ->where("user_id", "=", $user->id)
                    ->whereHas('ticket', function ($q) use($bP) {
                        $q->whereHas("match", function ($q) use($bP) {
                            $q->where("betting_provider_id","=", $bP->id);
                        });
                    });

                // bet them baby
                foreach ($userTickets as $userTicket) {
                    $userTicket->bet($this);

//                    sleep(rand(10,20));
                }

                // in the end update credit
                $user->updateCredit($bP->id);
            }

        }

    }

}