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

class LoginCommand extends Command
{

    protected $signature  = "login";
    protected $description = "Login me in";

    public function handle() {

        try {

            $user = new User();

            if ($user->isLoggedIn()) {
                $this->info("User is logged in. Nothing done.");
            } else {
                if ($user->login()) {
                    $this->info("User was successfully logged in.");
                } else {
                    $this->error("User was not logged in. Error happened.");
                }
            }

        } catch(\Throwable $e) {
            // we need something for logging
            throw $e;
        }

    }
}