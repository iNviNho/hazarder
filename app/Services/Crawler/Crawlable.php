<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 14.10.18
 * Time: 12:02
 */

namespace App\Services\Crawler;


interface Crawlable
{

    public function crawl();

    public function parseAndPersistMatches();

    public function updateMatch($match, $data);

}