<?php

namespace App;

use BCMathExtended\BC;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BettingProvider extends Model
{

    const FIRST_PROVIDER_F = 1;
    const SECOND_PROVIDER_N = 2;

    public static function isEnabled($bettingProviderID) {
        return BettingProvider::find($bettingProviderID)->active == 1 ? true : false;
    }

    public static function isHisTime($bettingProviderID) {

        $hour = (Carbon::now())->hour;

        if ($bettingProviderID == self::FIRST_PROVIDER_F) {
            return BC::comp($hour, 16) >= 0;
        } elseif ($bettingProviderID == self::SECOND_PROVIDER_N) {
            return BC::comp($hour, 16) < 0;
        }

    }

}
