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
use Illuminate\Support\Facades\Auth;

class User
{

    /** @var Client */
    private $guzzleClient;

    /** @var FileCookieJar */
    private $cookieJar;

    public function __construct()
    {
        // when working with user we should always have cookies prepared
        $this->cookieJar = new FileCookieJar("cookie_jar.txt", TRUE);
        // every request from this class will use its cookies
        $this->guzzleClient = new Client([
            'headers' => AppSettings::getHeaders(),
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

        $settings = Auth::user()->getSettings();
        dd($settings);

        //login
        $body = [
            "form_params" => [
                "username" => env("LOGIN_LOGIN"),
                "password" => env("LOGIN_PASSWORD")
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
            'headers' => AppSettings::getHeaders(),
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