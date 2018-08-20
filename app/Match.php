<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{

    /**
     * Get the comments for the blog post.
     */
    public function getMatchBets()
    {
        return $this->hasMany('App\MatchBet');
    }

    public function afterMatchBetUpdate($command) {

        // get all tickets that were bet on this game
        $tickets = Ticket::where("match_id", $this->id)->get();

        // for all tickets
        foreach ($tickets as $ticket) {

            // check if ticket was not bet already
            $userTickets = UserTicket::where("ticket_id", $ticket->id);

            // if this ticket was not bet already
            if ($userTickets->count() < 1) {

                // lets delete it so it can be recreated with nearest tickets:prepare command
                $ticket->delete();

                $command->info("Ticket with ID: " . $ticket->id . " was successfully deleted after matchbets were updated");
            }

        }

    }

}
