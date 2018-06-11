<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 10.06.18
 * Time: 00:14
 */

namespace App\Services;


class AppSettings
{

    public static function getHeaders() {
        return ["User-Agent" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.170 Safari/537.36"];
    }

    public static function setMaxFileSize() {
        // max file size for html dom crawler, otherwise it would fail
        define("MAX_FILE_SIZE", 99999999);
    }

}