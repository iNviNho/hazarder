<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 15.05.18
 * Time: 21:49
 */

namespace App\Services\Crawler;


use App\Console\Commands\CrawlCommand;
use App\Match;
use App\Services\AppSettings;
use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;

class Crawler
{

    private $crawlCommand;
    private $guzzleClient;

    private $rawMatches = [];

    /**
     * Crawler constructor.
     * Lets setup and prepare
     */
    public function __construct(CrawlCommand $crawlCommand)
    {
        $this->crawlCommand = $crawlCommand;
        $this->guzzleClient = new Client([
            'headers' => AppSettings::getHeaders()
        ]);

        // max file size for html dom crawler, otherwise it would fail
        define("MAX_FILE_SIZE", 99999999);
    }

    /**
     * Lets start crawling and return
     */
    public function start() {

        $this->crawlCommand->info("Crawling started ...");
        $this->parseAllTodayMatches();

        $this->done();
    }

    /**
     * Prepare every URL that will be parsed
     */
    private function parseAllTodayMatches() {

        $start = 0;
        while (true) {
            $todayURL = env("TODAY_MATCHES_URL");
            $todayURL = sprintf($todayURL, $start);
            $start += 100;

            $response = $this->guzzleClient->get($todayURL)->getBody()->getContents();

            $htmlParser = HtmlDomParser::str_get_html($response);

            $categories = $htmlParser->find("div[class=box def nopadding]");

            // no more games
            if (count($categories) < 1) {
                break;
            }
            foreach ($categories as $category) {
                $categoryName = $category->find(".bet_table_top_box", 0)
                    ->find("h3", 0)
                    ->plaintext;

                $rawMatches = $category->find(".bet_table_holder", 0)
                    ->find("table", 0)
                    ->lastChild()
                    ->children();
                foreach ($rawMatches as $rawMatch) {
                    // we dont want live bets
                    $rawMatchClass = $rawMatch->getAttribute("class");
                    if (strpos($rawMatchClass, "live_running_bet") !== false) {
                        continue;
                    }
                    $match = new Match();

                    $match->unique_id = $rawMatch->getAttribute("data-id");
                    $match->category = $categoryName;
                    $match->name = $rawMatch->find(".bet_item_detail_href", 0)
                        ->plaintext;
                    $match->date_of_game = $rawMatch->find(".col_date", 0)->find("span", 0)->plaintext;

                    try {
                        $match->a = $rawMatch->find(".add_bet_link-0", 0)->getAttribute("data-rate");
                        $match->b = $rawMatch->find(".add_bet_link-1", 0)->getAttribute("data-rate");
                        $match->c = $rawMatch->find(".add_bet_link-2", 0)->getAttribute("data-rate");
                        $match->ab = $rawMatch->find(".add_bet_link-3", 0)->getAttribute("data-rate");
                        $match->bc = $rawMatch->find(".add_bet_link-4", 0)->getAttribute("data-rate");
                    } catch (\Throwable $e) {
                    }

                    $this->rawMatches[] = $match;
                }

            }

            // we cannot be that fast
            usleep(1000000);
        }

        $this->crawlCommand->info("Prepare URLs done");
    }

    /**
     * Function called after crawl command is done
     */
    private function done() {
        $this->crawlCommand->info("Crawl command done");
    }

    /**
     * @return array
     */
    public function getRawMatches()
    {
        return $this->rawMatches;
    }

}