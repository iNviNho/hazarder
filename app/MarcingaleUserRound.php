<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarcingaleUserRound extends Model
{

    /**
     * Get the comments for the blog post.
     */
    public function getMarcingaleUserTickets()
    {
        return $this->hasMany('App\MarcingaleUserTicket');
    }

}
