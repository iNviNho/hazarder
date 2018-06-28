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
use App\MatchBet;
use App\Services\AppSettings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;

class Crawler
{

    private $crawlCommand;
    private $guzzleClient;

    private $rawURLs = [];
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

    }

    /**
     * Lets start crawling and return
     */
    public function crawlAndInsert() {

        $this->crawlCommand->info("Crawling started ...");

        $this->prepareURLS();

        $this->parseAllTodayMatches();

    }

    private function prepareURLS() {

        $now = Carbon::now()->getTimestamp();
        $url = $this->guzzleClient->get(env("BASE_TODAY_GROUPS_URL"));

        $html = HtmlDomParser::str_get_html($url->getBody()->getContents());

        $contentDivs = $html->find("div[class=content scrollable-area]", 0)->children();

        foreach ($contentDivs as $contentDiv) {

            $href = $contentDiv->find("a[class=header]", 0)->getAttribute("href");

            $groupURL = env("BASE_URL") . "/ajax" . $href . "&_ts=" . $now;
            $this->rawURLs[] = $groupURL;
        }

        $this->crawlCommand->info("Prepare URLs done");
    }

    /**
     * Prepare every URL that will be parsed
     */
    private function parseAllTodayMatches() {

        foreach ($this->rawURLs as $groupURL) {
            $result = $this->guzzleClient->get($groupURL);

            $data = [
                "text" => $result->getBody()->getContents()
            ];
            $pes = view("welcome", $data)->render();

            $html = HtmlDomParser::str_get_html($pes);

            $groups = $html->find("div[class=content-box competition sub-box]");

            foreach ($groups as $group) {

                $games = $group->find("li[class=event]");

                // game is li class=event
                foreach ($games as $game) {


                    $match = new Match();

                    $match->unique_id = $game->getAttribute("id");

                    $match->category = $group->find("h3[class=title]", 0)->plaintext;
                    $match->category = trim(str_replace("\t", "", $match->category));

                    $match->name = trim($game->find("div[class=name]", 0)->plaintext);

                    $teams = explode("&times;", $match->name);
                    if (count($teams) > 1) {
                        $match->teama = trim($teams[0]);
                        // perform check for team a if it has (F)
                        $match->teama = trim( str_replace("F()", "", $match->teama) );
                        $match->teamb = trim($teams[1]);
                    }
                    $match->name = $match->teama . " vs " . $match->teamb;

                    $today = new Carbon();
                    $match->created_at = $today;

                    $match->date_of_game = $game->find("div[class=date]", 0)->plaintext;

                    if (strpos($match->date_of_game, 'DNES') === false) {
                        // it is "zajtra"
                        $match->date_of_game = str_replace('ZAJTRA', "", $match->date_of_game);
                        $date = explode(":", $match->date_of_game);

                        $dateOfGame = new Carbon();
                        $dateOfGame->setDateTime($today->year, $today->month, $today->day, $date[0], $date[1]);
                        $dateOfGame->addDay();

                        $match->date_of_game = $dateOfGame;
                    } else {
                        // it is "dnes"
                        $match->date_of_game = str_replace('DNES', "", $match->date_of_game);
                        $date = explode(":", $match->date_of_game);

                        $dateOfGame = new Carbon();
                        $dateOfGame->setDateTime($today->year, $today->month, $today->day, $date[0], $date[1]);

                        $match->date_of_game = $dateOfGame;
                    }

                    $match->unique_name = $match->name . ":" . $match->date_of_game->getTimestamp();

                    $match->save();

                    $bets = $game->find(".market", 0)->children();
                    foreach ($bets as $key => $bet) {

                        $matchBet = new MatchBet();
                        $matchBet->name = $bet->find("span[class=tip]", 0)->plaintext;
                        $matchBet->value = trim($bet->find("span[class=odd]", 0)->plaintext);

                        $matchBet->datainfo = $bet->getAttribute("data-info");
                        $matchBet->dataodd = $bet->getAttribute("data-odd");

                        $match->getMatchBets()->save($matchBet);
                    }

                    $matchBetsCount = $match->getMatchBets()->count();

                    if ($matchBetsCount == 5) {
                        $match->type = "normal";
                    }
                    if ($matchBetsCount == 4) {
                        $match->type = "weird";
                    }
                    if ($matchBetsCount == 3) {
                        $match->type = "simple";
                    }
                    if ($matchBetsCount == 2) {
                        $match->type = "goldengame";
                    }
                    if ($matchBetsCount == 1) {
                        $match->type = "single";
                    }

                    $this->rawMatches[] = $match;
                }
            }
        }


        $this->crawlCommand->info("Parse all games DONE");
    }

    /**
     * @return array
     */
    public function getRawMatches()
    {
        return $this->rawMatches;
    }

}