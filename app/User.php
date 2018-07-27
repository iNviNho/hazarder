<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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

    public function approveTickets($command, $tickets) {

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
                ->where("status", "=", "bet")
                ->get()->count();
            $allowedGameTypesToBet[$GAME_TYPE] -= $betTicketsCount;
        }

        foreach ($tickets as $ticket) {

            // can we approve this ticket for this game type?
            if ($allowedGameTypesToBet[$ticket->game_type] > 0) {

                $userTicket = new UserTicket();
                $userTicket->status = "approved";
                $userTicket->external_ticket_id = "-";
                $userTicket->user_id = $this->id;
                $userTicket->ticket_id = $ticket->id;

                $userTicket->bet_option = $ticket->matchbet->name;
                $userTicket->bet_amount = $userSettings->bet_amount;
                $userTicket->bet_rate = $ticket->matchbet->value;
                $userTicket->bet_possible_win = bcmul($userTicket->bet_amount, $userTicket->bet_rate, "2");
                $userTicket->bet_possible_clear_win = bcsub($userTicket->bet_possible_win, $userTicket->bet_amount, "2");

                $userTicket->bet_win = 0; // default we always obviously won 0 so far

                $userTicket->save();

                // -1 allowed bet for this game type
                $allowedGameTypesToBet[$ticket->game_type] -= 1;

                $command->info("UserTicket with ID: " . $userTicket->id . " was successfully create for user with ID: " . $this->id);
            }

        }

    }

}