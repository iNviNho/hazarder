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

    public static function getUserHeaders() {
        return [
            1 => "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0",
            2 => "Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0",
            3 => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36",
            4 => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36 OPR/38.0.2220.41",
            5 => "Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1",
        ];
    }

    public static function setMaxFileSize() {
        // max file size for html dom crawler, otherwise it would fail
        define("MAX_FILE_SIZE", 99999999);
    }

}