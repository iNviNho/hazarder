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
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketsCheckResultCommand extends Command
{

    protected $signature  = "tickets:checkresult";
    protected $description = "Check results of all bet tickets";

    public function handle() {

        $this->info("Check latest tickets");

        // first loop through user betting provider
        $bettingProviders = BettingProvider::all();
        foreach ($bettingProviders as $bP) {

            // is this bettingProvider enabled?
            if (!BettingProvider::isEnabled($bP->id)) {
                continue;
            }

            // can this betting provider run at this time?
            if (!BettingProvider::isHisTime($bP->id)) {
                continue;
            }

            // then loop through users
            $users = User::all()
                ->where("is_authorized", "=", "1");
            foreach ($users as $user) {

                // did user enabled it?
                if (!Settings::isBettingProviderEnabled($user->id, $bP->id)) {
                    continue;
                }

                // now do the checking
                // get all bet user tickets
                $userTickets = UserTicket::where("status", "=", "bet")
                    ->where("user_id", "=", $user->id)
                    ->whereHas('Ticket', function ($q) use($bP) {
                        $q->whereHas("Match", function ($q) use($bP) {
                            $q->where("betting_provider_id","=", $bP->id);
                        });
                    });
                $nowTimestamp = Carbon::now()->getTimestamp();
                foreach ($userTickets->get() as $userTicket) {

                    // game had to be played at least 2.5 hours ago
                    $matchFinishTime = Carbon::createFromTimeString($userTicket->ticket->match->date_of_game)->addMinutes(150)->getTimestamp();

                    if ($nowTimestamp > $matchFinishTime) {
                        $userTicket->tryToCheckResult($this);
                        sleep(rand(5, 15));
                    }

                }

                $user->updateCredit($bP->id);
            }

        }

        $this->info("Check for check results was done.");
    }

}