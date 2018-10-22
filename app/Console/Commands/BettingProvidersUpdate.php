<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\BettingProvider;
use App\Services\AppSettings;
use App\Settings;
use App\User;
use Illuminate\Console\Command;

class BettingProvidersUpdate extends Command
{

    protected $signature  = "betting_providers:update";
    protected $description = "Approve tickets";

    public function handle() {

        $this->info("Update settings for betting providers");

        $bettingProviders = BettingProvider::all();
        $users = User::all();

        foreach ($users as $u) {
            foreach ($bettingProviders as $bP) {

                $hasSettingsForThisBP = Settings::where([
                    "user_id" => $u->id,
                    "betting_provider_id" => $bP->id
                ])->first();


                // if user does not have a settings for this BP, lets add them
                if (is_null($hasSettingsForThisBP)) {

                    // create settings for user
                    $settings = new Settings();
                    $settings->username = "-";
                    $settings->password = "-";
                    $settings->max_marcingale = "0";
                    $settings->bet_amount = "0.5";
                    $settings->user_id = $u->id;
                    $settings->max_marcingale_level = "0";

                    // header stuff
                    $countHeaders = count(AppSettings::getUserHeaders());
                    $random = rand(1, $countHeaders);
                    $newHeader = AppSettings::getUserHeaders()[$random];
                    $settings->header = $newHeader;

                    $settings->marcingale_finish = "0";
                    $settings->active = "0";
                    $settings->betting_provider_id = $bP->id;
                    $settings->save();

                    $this->info("Settings for users and betting providers were inserted for user $u->id and betting provider $bP->id ");
                }

            }
        }

        $this->info("Settings for users and betting providers were updated");
    }

}