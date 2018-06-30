<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Services\Crawler\Crawler;
use Illuminate\Console\Command;

class CrawlCommand extends Command
{

    protected $signature  = "crawl";
    protected $description = "Start crawling";

    public function handle() {

        try {

            $crawler = new Crawler($this);
            $crawler->crawlAndInsert();

            $this->info("Crawling is DONE");

        } catch(\Throwable $e) {
            // we need something for logging
            throw $e;
        }

    }
}