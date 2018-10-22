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
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketsApproveCommand extends Command
{

    protected $signature  = "tickets:approve";
    protected $description = "Approve tickets";

    public function handle() {

        $this->info("Approve tickets for all users from prepared tickets");

        $bettingProviders = BettingProvider::all();
        foreach ($bettingProviders as $bP) {

            // is this bettingProvider enabled?
            if (!BettingProvider::isEnabled($bP->id)) {
                continue;
            }

            // prepare all tickets for this betting provider
            $tickets = Ticket::select(["tickets.*", "matches.date_of_game", "matches.betting_provider_id"])
                ->join('matches', 'matches.id', '=', 'tickets.match_id')
                ->where("status", "=", "prepared")
                ->where("betting_provider_id", "=", $bP->id)
                ->where('date_of_game', '>=', Carbon::now()->addMinutes(15)->format("Y-m-d H:i:s"))
                ->orderBy("date_of_game", "asc");

            $users = User::all()
                ->where("is_authorized", "=", "1");

            foreach ($users as $user) {

                // check if this user has this betting provider active
                if (!Settings::isBettingProviderEnabled($user->id, $bP->id)) {
                    continue;
                }

                $user->approveTickets($tickets, $bP->id);
            }

        }

    }

}