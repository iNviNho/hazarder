<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    protected $guarded = array();

    public function bettingProvider()
    {
        return $this->belongsTo('App\BettingProvider');
    }

    public static function isBettingProviderEnabled($userID, $bettingProviderID) {

        $settings = Settings::where([
            "user_id" => $userID,
            "betting_provider_id" => $bettingProviderID
        ])->first();

        return $settings->active == "1" ? true : false;
    }

}
