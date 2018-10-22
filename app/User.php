<?php
namespace App;

use App\Events\UserLogEvent;
use App\Services\AppSettings;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Sunra\PhpSimple\HtmlDomParser;
use BCMathExtended\BC;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Approve given tickets for given bettingProviderID
     * @param $tickets
     * @param $bettingProviderID
     */
    public function approveTickets($tickets, $bettingProviderID) {

        $userSettings = $this->getSettings($bettingProviderID);
        $credit = $userSettings->credit;

        $allowedGameTypesToBet = [];
        // get max value for $GAME_TYPE
        foreach (Ticket::$GAME_TYPES as $GAME_TYPE) {
            $settingsValue = "max_" . $GAME_TYPE;
            $allowedGameTypesToBet[$GAME_TYPE] = (int)$userSettings->$settingsValue;
        }

        // decrease allowed games to bet based on already bet games
        foreach (Ticket::$GAME_TYPES as $GAME_TYPE) {

            $betTicketsCount = UserTicket::whereHas('ticket', function ($q) use($GAME_TYPE, $bettingProviderID) {
                    $q->where('game_type', '=', $GAME_TYPE); // concrete game_type
                    $q->whereHas("match", function ($q) use($bettingProviderID) {
                        $q->where("betting_provider_id", $bettingProviderID); // current betting provider
                    });
                })
                ->where("user_id", "=", $this->id) // belongs to user
                ->where(function ($q) { // is either bet or approved
                    $q->where("status", "=", "bet")
                        ->orWhere("status", "=", "approved");
                })
                ->get()->count();
            $allowedGameTypesToBet[$GAME_TYPE] -= $betTicketsCount;
        }

        foreach ($tickets->get() as $ticket) {

            // safety check to approve only the ones we intend to
            if ($ticket->match()->first()->betting_provider_id != $bettingProviderID) {
                continue;
            }

            // can we approve this ticket for this game type?
            if ($allowedGameTypesToBet[$ticket->game_type] > 0) {

                // lets do a safety check if this ticket id was not already approved and made as a user ticket
                // this will prevent having 2 user tickets for same UserTicket
                $userTicketsForThisTicket = UserTicket::where([
                        "ticket_id" => $ticket->id,
                        "user_id" => $this->id
                    ])->count();
                if ($userTicketsForThisTicket > 0) {
                    // continue to next iteration and don't process this ticket
                    continue;
                }

                $userTicket = new UserTicket();
                $userTicket->status = "approved";
                $userTicket->external_ticket_id = "-";
                $userTicket->user_id = $this->id;
                $userTicket->ticket_id = $ticket->id;

                $userTicket->bet_option = $ticket->matchbet->name;

                // we use different code and flow for marcingale
                if ($ticket->game_type == "marcingale") {

                    // first immediately check if this user maybe wants to bet only on favorits
                    // and if yes, this matchBet has to be bet on favorit
                    if ($userSettings->only_favorits == 1 && !Ticket::isMatchBetFavoritInThisMatch($ticket->match()->first(), $ticket->matchBet()->first())) {
                        continue;
                    }

                    // lets check if by any chance we don't have already bet on this match
                    // in marcingale don't do it, even if the odd is now on opposite match
                    $alreadyBetOnMatchTimes = UserTicket::whereHas("ticket", function($q) use($ticket) {
                            $q->where("match_id", $ticket->match_id);
                            $q->where("game_type", "marcingale");
                        })
                        ->where("user_id", $this->id)
                        ->count();
                    if ($alreadyBetOnMatchTimes > 0) {
                        continue;
                    }

                    $shouldWeCreateNewMarcingaleTicketRound = MarcingaleUserTicket::shouldWeCreateNewMarcingaleTicketRound($this);
                    if ($shouldWeCreateNewMarcingaleTicketRound === true) {
                        // if user did set finish marcingale to 1, we dont create new marcingale user tickets
                        if ($userSettings->marcingale_finish == 1) {
                            continue;
                        }

                        $marcingaleUserTicket = MarcingaleUserTicket::createFreshMarcingaleUserTicketRound($this);
                        $userTicket->bet_amount = $userSettings->bet_amount;
                    } else {
                        $marcingaleUserTicket = MarcingaleUserTicket::createContinuousMarcingaleUserTicket($this, $shouldWeCreateNewMarcingaleTicketRound);
                        $userTicket->bet_amount = MarcingaleUserTicket::getBetAmountForContinuousUserTicket($shouldWeCreateNewMarcingaleTicketRound, $marcingaleUserTicket->level);
                    }

                } else {
                    $userTicket->bet_amount = $userSettings->bet_amount;
                }

                // does user still have credit
                if (BC::comp($credit, $userTicket->bet_amount, 2) < 0) {
                    event(new UserLogEvent("Unable to create marcingale bet. User credit " . $credit . " too low for bet " . $userTicket->bet_amount, $this->id));
                    continue;
                }

                $userTicket->bet_rate = $ticket->matchbet->value;
                // hotfix for bad bet_rates
                if ( ($userTicket->bet_rate == "") || ($userTicket->bet_rate == " ")) {
                    continue;
                }

                $userTicket->bet_possible_win = BC::mul($userTicket->bet_amount, $userTicket->bet_rate, 3);
                $userTicket->bet_possible_win = BC::roundUp($userTicket->bet_possible_win, 2);

                $userTicket->bet_possible_clear_win = bcsub($userTicket->bet_possible_win, $userTicket->bet_amount, "2");

                $userTicket->bet_win = 0; // default, we always obviously won 0 so far

                $userTicket->save();

                // don't forget to set user_ticket_id for marcingale user tickets
                if ($ticket->game_type == "marcingale") {
                    $marcingaleUserTicket->user_ticket_id = $userTicket->id;
                    $marcingaleUserTicket->save();
                }

                // -1 allowed bet for this game type
                $allowedGameTypesToBet[$ticket->game_type] -= 1;

                // lower credit
                $credit = BC::sub($credit, $userTicket->bet_amount, 2);

                event(new UserLogEvent("UserTicket for game type: " . $ticket->game_type . " with ID: " . $userTicket->id . " created.", $this->id, $userTicket->id));
            }

        }

    }

    public function updateCredit() {

        // first insert into basket
        $user = new \App\Services\User\User($this);
        if (!$user->login()) {
            event(new UserLogEvent("Failed login while updating credit", $this->user->id));
            return;
        }
        $guzzleClient = $user->getUserGuzzle();

        // clear ticket & have fresh one
        $baseUrl = $guzzleClient->get(env("BASE_URL"));

        $ticketSummaryHtml = HtmlDomParser::str_get_html($baseUrl->getBody()->getContents());

        $credit = $ticketSummaryHtml->find("strong[class=credit]", 0)->plaintext;

        $this->credit = trim($credit);
        $this->credit_update_time = Carbon::now();

        $this->save();

        event(new UserLogEvent("Users credit with ID: " . $this->id . " was successfully updated to: " . $this->credit, $this->id));
    }

    public function getSettings($bettingProviderID) {
        return Settings::where([
            "user_id" => $this->id,
            "betting_provider_id" => $bettingProviderID
        ])->first();
    }

    public function getCreditUpdateTime($bettingProviderID) {

        return Carbon::createFromTimeString($this->getSettings($bettingProviderID)->credit_update_time);
    }

    public function getHeader($bettingProviderID) {

        $settings = Settings::where([
            "user_id" => $this->id,
            "betting_provider_id" => $bettingProviderID
        ])->first();

        // get header
        return ["User-Agent" => $settings->header];
    }

    public function getCredit() {

        $credit = Settings::where(["user_id" => $this->id])->selectRaw("sum(credit) as credit")->pluck("credit")->first();

        return $credit;
    }

}