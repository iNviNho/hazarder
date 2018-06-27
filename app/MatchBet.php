<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchBet extends Model
{

    protected $table = "matchbets";


    /**
     * Get the post that owns the comment.
     */
    public function match()
    {
        return $this->belongsTo('App\Match');
    }

}
