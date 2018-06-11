<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 13.05.18
 * Time: 13:25
 */

namespace App\Console\Commands;


use App\Model\Entity\MatchOld;
use App\Services\Crawler\Crawler;
use App\Services\Match\MatchService;
use Cake\ORM\TableRegistry;
use Illuminate\Console\Command;

class CrawlCommand extends Command
{

    protected $signature  = "crawl";
    protected $description = "Start crawling";

    public function handle() {

        try {

            $crawler = new Crawler($this);
            $crawler->start();

            $matchService = new MatchService();
            $matchService->insertGames($crawler->getRawMatches());

            $this->info("Crawling is done");

        } catch(\Throwable $e) {
            // we need something for logging
            throw $e;
        }

    }
}