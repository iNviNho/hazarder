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

}
