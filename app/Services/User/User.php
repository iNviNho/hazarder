<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 09.06.18
 * Time: 21:23
 */

namespace App\Services\User;

use App\BettingProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;

class User
{

    /**
     * We perform login of current user for specified betting provider
     * @return bool
     */
    public function login(\App\User $userEntity, $bettingProviderID) {

        // if user is logged in, just return true
        if ($this->isLoggedIn($userEntity, $bettingProviderID)) {
            return true;
        }

        $guzzleClient = $this->getGuzzleForUserAndBP($userEntity, $bettingProviderID);

        // login for first provider
        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            //login
            $body = [
                "form_params" => [
                    "username" => $userEntity->getSettings()->first()->username,
                    "password" => $userEntity->getSettings()->first()->password,
                ]
            ];
            $guzzleClient->post( env("LOGIN_URL_FIRST_BETTING_PROVIDER_F"), $body);

        }

        // login for second provider
        if ($bettingProviderID == BettingProvider::SECOND_PROVIDER_N) {

            //login
            $body = [
                "form_params" => [
                    "meno" => $userEntity->getSettings()->first()->username,
                    "heslo" => $userEntity->getSettings()->first()->password,
                ]
            ];
            $response = $guzzleClient->post( env("LOGIN_URL_SECOND_BETTING_PROVIDER_N"), $body);

        }

        return $this->isLoggedIn($userEntity, $bettingProviderID);
    }

    /**
     * Returns bool whether the user is logged in for specified betting provider
     * @param \App\User $userEntity
     * @param $bettingProviderID
     * @return bool
     */
    private function isLoggedIn(\App\User $userEntity, $bettingProviderID) {

        $guzzleClient = $this->getGuzzleForUserAndBP($userEntity, $bettingProviderID);

        // check if we are logged in for first betting provider
        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            try {
                $response = $guzzleClient->get(env("USER_LOGGED_IN_CHECK_FIRST_BETTING_PROVIDER_F"));
            } catch (\Throwable $e) {
                return false;
            }

            // is he logged in
            if ($response->getStatusCode() == 200) {
                return false;
            } else {
                return true;
            }

        }

        // check if we are logged in for second betting provider
        if ($bettingProviderID == BettingProvider::SECOND_PROVIDER_N) {

            try {
                $response = $guzzleClient->get(env("USER_LOGGED_IN_CHECK_SECOND_BETTING_PROVIDER_N"));
//                dd($response);
            } catch (\Throwable $e) {
//                throw $e;
                return false;
            }

            // is he logged in
            if ($response->getStatusCode() == 200) {
                return true;
            } else {
                return false;
            }

        }

    }

    /**
     * Prepare and return custom guzzle client for $userEntity and $bettingProviderID
     * @param \App\User $userEntity
     * @param $bettingProviderID
     * @return Client
     */
    private function getGuzzleForUserAndBP(\App\User $userEntity, $bettingProviderID) {

        // prepare users cookieJar
        $cookieJar = new FileCookieJar("cookie_jar_user_id_" . $userEntity->id . "_betting_provider_" . $bettingProviderID . ".txt", TRUE);
        // get user header
        $header = $userEntity->getHeader();

        // return prepared Guzzle client
        return new Client([
            'headers' => $header,
            "cookies" => $cookieJar,
            'allow_redirects' => false
        ]);
    }

}