<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;

use App\Services\Crawler\CrawlerFirst;
use App\Services\Crawler\CrawlerSecond;
use Illuminate\Console\Command;

class CrawlCommand extends Command
{

    protected $signature  = "crawl";
    protected $description = "Start crawling";

    public function handle() {

        try {

            $crawler = new CrawlerFirst($this);
            $crawler->crawl();

            $this->info("Crawling of first betting provider DONE");

            $crawler = new CrawlerSecond($this);
            $crawler->crawl();

            $this->info("Crawling of second betting provider DONE");

        } catch(\Throwable $e) {
            // we need something for logging
            throw $e;
        }

    }
}