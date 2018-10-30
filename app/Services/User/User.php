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
use GuzzleHttp\RequestOptions;

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

        $guzzleClient = $this->getGuzzleForUserAndBP($userEntity, $bettingProviderID, [
            "X-Requested-With" => "XMLHttpRequest",
        ]);

        // login for first provider
        if ($bettingProviderID == BettingProvider::FIRST_PROVIDER_F) {

            //login
            $body = [
                RequestOptions::FORM_PARAMS => [
                    "username" => $userEntity->getSettings($bettingProviderID)->first()->username,
                    "password" => $userEntity->getSettings($bettingProviderID)->first()->password,
                ]
            ];
            $guzzleClient->post( env("LOGIN_URL_FIRST_BETTING_PROVIDER_F"), $body);

            sleep(2);
        }

        // login for second provider
        if ($bettingProviderID == BettingProvider::SECOND_PROVIDER_N) {

            //login
            $loginParams = new \stdClass();
            $loginParams->meno = $userEntity->getSettings($bettingProviderID)->first()->username;
            $loginParams->heslo = $userEntity->getSettings($bettingProviderID)->first()->password;

            $body = [
                RequestOptions::FORM_PARAMS => $loginParams
            ];
            $guzzleClient->post( env("LOGIN_URL_SECOND_BETTING_PROVIDER_N"), $body);

            sleep(2);
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
            } catch (\Throwable $e) {
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
    public function getGuzzleForUserAndBP(\App\User $userEntity, $bettingProviderID, $additionalHeaders = []) {

        // prepare users cookieJar
        $cookieJar = new FileCookieJar("cookie_jar_user_id_" . $userEntity->id . "_betting_provider_" . $bettingProviderID . ".txt", TRUE);
        // get user header
        $header = array_merge($userEntity->getHeader($bettingProviderID), $additionalHeaders);

        // return prepared Guzzle client
        return new Client([
            "http_errors" => false,
            'headers' => $header,
            "cookies" => $cookieJar,
            'allow_redirects' => false
        ]);
    }

}