<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 09.06.18
 * Time: 21:23
 */

namespace App\Services\User;

use App\Services\AppSettings;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;

class User
{

    /** @var Client */
    private $guzzleClient;

    /** @var FileCookieJar */
    private $cookieJar;

    /** @var \App\User */
    private $user;

    /** @var mixed  */
    private $userHeader;

    public function __construct(\App\User $user)
    {
        $this->user = $user;
        // when working with user we should always have cookies prepared
        $this->cookieJar = new FileCookieJar("cookie_jar_" . $this->user->id . ".txt", TRUE);
        // get user header
        $this->userHeader = $user->getHeader();
        // every request from this class will use its cookies
        $this->guzzleClient = new Client([
            'headers' => $this->userHeader,
            "cookies" => $this->cookieJar
        ]);

    }

    /**
     * We perform login of current user
     * @return bool
     */
    public function login() {

        // if user is logged in, we can just return true
        if ($this->isLoggedIn()) {
            return true;
        }

        //login
        $body = [
            "form_params" => [
                "username" => $this->user->getSettings()->first()->username,
                "password" => $this->user->getSettings()->first()->password,
            ]
        ];
        $this->guzzleClient->post( env("LOGIN_URL"), $body);

        return $this->isLoggedIn();
    }

    /**
     * Logs out user
     * Returns boolean wheather was action successful
     * @return bool
     */
    public function logout() {
        $this->guzzleClient->get(env("LOGOUT_URL"));

        return !$this->isLoggedIn();
    }

    /**
     * Is user logged in?
     * @return bool
     */
    public function isLoggedIn() {

        // every request from this class will use its cookies
        $this->guzzleClient = new Client([
            'headers' => $this->userHeader,
            "cookies" => $this->cookieJar,
            'allow_redirects' => false
        ]);
        $response = $this->guzzleClient->get(env("LOGIN_URL_FOR_LOGGED_IN_CHECK"));

        // is he logged in
        if ($response->getStatusCode() == 200) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Return guzzle client
     * @return Client
     */
    public function getUserGuzzle() {
        return $this->guzzleClient;
    }

}