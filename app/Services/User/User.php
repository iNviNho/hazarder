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
use Illuminate\Console\Command;
use Sunra\PhpSimple\HtmlDomParser;

class User
{

    /** @var Command */
    private $command;

    /** @var Client */
    private $guzzleClient;

    /** @var FileCookieJar */
    private $cookieJar;

    public function __construct(Command $command)
    {
        $this->command = $command;

        // when working with user we should always have cookies prepared
        $this->cookieJar = new FileCookieJar("cookie_jar.txt", TRUE);
        // every request from this class will use its cookies
        $this->guzzleClient = new Client([
            'headers' => AppSettings::getHeaders(),
            "cookies" => $this->cookieJar
        ]);

        // max file size for html dom crawler, otherwise it would fail
        define("MAX_FILE_SIZE", 99999999);
    }

    /**
     * We perform login of current user
     * @return bool
     */
    public function login() {

        //login
        $body = [
            "form_params" => [
                "login" => env("LOGIN_LOGIN"),
                "password" => env("LOGIN_PASSWORD"),
                "cid" => "",
                "redirectTo" => env("LOGIN_REDIRECT_TO")
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

        $response = $this->guzzleClient->get(env("BASE_URL"))->getBody()->getContents();

        $htmlParser = HtmlDomParser::str_get_html($response);

        $result = $htmlParser->find("#user-menu-toggler");
        // is he logged in
        if (count($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

}