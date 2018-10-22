<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    public function bettingProvider()
    {
        return $this->belongsTo('App\BettingProvider');
    }

    protected $guarded = array();

}
