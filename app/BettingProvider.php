<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BettingProvider extends Model
{

    const FIRST_PROVIDER_F = 1;
    const SECOND_PROVIDER_N = 2;

    public static function isEnabled($bettingProviderID) {

        return BettingProvider::find($bettingProviderID)->active == 1 ? true : false;

    }

}
