<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTicket extends Model
{

    protected $table = "user_tickets";

    /**
     * Get the comments for the blog post.
     */
    public function ticket()
    {
        return $this->belongsTo('App\Ticket');
    }



}
