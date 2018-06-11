<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;


use App\Services\User\User;
use Illuminate\Console\Command;

class LogoutCommand extends Command
{

    protected $signature  = "logout";
    protected $description = "Log me out";

    public function handle() {

        try {

            $user = new User($this);

            if ($user->isLoggedIn()) {

                if ($user->logout()) {
                    $this->info("User was successfully logged out.");
                } else {
                    $this->error("Logging out failed. Error happened. User is still logged in.");
                }
            } else {
                $this->info("User is logged out. Nothing done.");
            }

        } catch(\Throwable $e) {
            // we need something for logging
            throw $e;
        }

    }
}