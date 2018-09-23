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
     * Get the comments for the blog post.
     */
    public function getSettings()
    {
        return $this->hasMany('App\Settings');
    }

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

    public function approveTickets($tickets) {

        $userSettings = $this->getSettings()->first();

        $allowedGameTypesToBet = [];

        // default it
        foreach (Ticket::$GAME_TYPES as $GAME_TYPE) {
            $settingsValue = "max_" . $GAME_TYPE;
            $allowedGameTypesToBet[$GAME_TYPE] = (int)$userSettings->$settingsValue;
        }

        // decrease allowed games to bet based on already bet games
        foreach (Ticket::$GAME_TYPES as $GAME_TYPE) {
            $betTicketsCount = UserTicket::whereHas('ticket', function ($q) use($GAME_TYPE) {
                    $q->where('game_type', '=', $GAME_TYPE);
                })
                ->where("user_id", "=", $this->id)
                ->where(function ($q) {
                    $q->where("status", "=", "bet")
                        ->orWhere("status", "=", "approved");
                })
                ->get()->count();
            $allowedGameTypesToBet[$GAME_TYPE] -= $betTicketsCount;
        }

        foreach ($tickets->get() as $ticket) {

            // can we approve this ticket for this game type?
            if ($allowedGameTypesToBet[$ticket->game_type] > 0) {

                // lets do a safety check if this ticket id was not already approved and made as a user ticket
                // this will prevent having 2 user tickets for same UserTicket
                $userTicketsForThisTicket = UserTicket::where("ticket_id", $ticket->id)->where("user_id", $this->id)->count();
                if ($userTicketsForThisTicket > 0) {
                    // continue to next iteration and dont process this ticket
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


                    $shouldWeCreateNewMarcingaleTicketRound = MarcingaleUserTicket::shouldWeCreateNewMarcingaleTicketRound($this);
                    if (MarcingaleUserTicket::shouldWeCreateNewMarcingaleTicketRound($this) === true) {
                        $marcingaleUserTicket = MarcingaleUserTicket::createFreshMarcingaleUserTicketRound($this);
                        $userTicket->bet_amount = $userSettings->bet_amount;
                    } else {
                        $marcingaleUserTicket = MarcingaleUserTicket::createContinuousMarcingaleUserTicketRound($this, $shouldWeCreateNewMarcingaleTicketRound);
                        $userTicket->bet_amount = MarcingaleUserTicket::getBetAmountForContinuousUserTicket($shouldWeCreateNewMarcingaleTicketRound, $marcingaleUserTicket->level);
                    }

                } else {
                    $userTicket->bet_amount = $userSettings->bet_amount;
                }

                $userTicket->bet_rate = $ticket->matchbet->value;
                if ( ($userTicket->bet_rate == "") || ($userTicket->bet_rate == " ")) {
                    // hotfix for bad bet_rates
                    continue;
                }
                $userTicket->bet_possible_win = BC::mul($userTicket->bet_amount, $userTicket->bet_rate, 3);
                $userTicket->bet_possible_win = BC::roundUp($userTicket->bet_possible_win, 2);

                $userTicket->bet_possible_clear_win = bcsub($userTicket->bet_possible_win, $userTicket->bet_amount, "2");

                $userTicket->bet_win = 0; // default we always obviously won 0 so far

                $userTicket->save();

                // dont forget to set user_ticket_id for marcingale user tickets
                if ($ticket->game_type == "marcingale") {
                    $marcingaleUserTicket->user_ticket_id = $userTicket->id;
                    $marcingaleUserTicket->save();
                }

                // -1 allowed bet for this game type
                $allowedGameTypesToBet[$ticket->game_type] -= 1;

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

    public function getCreditUpdateTime() {
        return Carbon::createFromTimeString($this->credit_update_time);
    }

    public function getHeader() {

        $settings = $this->getSettings()->first();
        // if header is null, append one
        if (is_null($settings->header)) {
            $countHeaders = count(AppSettings::getUserHeaders());
            $random = rand(1, $countHeaders);
            $newHeader = AppSettings::getUserHeaders()[$random];
            $settings->header = $newHeader;

            //save new header
            $settings->save();
        }

        // get header
        return ["User-Agent" => $this->getSettings()->first()->header];
    }

}