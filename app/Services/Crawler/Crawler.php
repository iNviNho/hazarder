<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 15.05.18
 * Time: 21:49
 */

namespace App\Services\Crawler;


use App\Match;
use App\MatchBet;
use App\Services\AppSettings;
use App\Services\Match\MatchService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Sunra\PhpSimple\HtmlDomParser;

class Crawler
{

    private $crawlCommand;
    private $guzzleClient;

    private $rawURLs = [];

    /**
     * Crawler constructor.
     * Lets setup and prepare
     * @param $crawlCommand
     */
    public function __construct(Command $crawlCommand)
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

    /**
     * Prepare every URL that will be parsed
     */
    private function prepareURLS() {

        $this->crawlCommand->info("Starting preparing URLs");

        $now = Carbon::now()->getTimestamp();
        $url = $this->guzzleClient->get(env("BASE_TODAY_GROUPS_URL"));

        $html = HtmlDomParser::str_get_html($url->getBody()->getContents());

        $contentDivs = $html->find("div[class=content scrollable-area]", 0)->children();

        foreach ($contentDivs as $contentDiv) {

            $href = $contentDiv->find("a[class=header]", 0)->getAttribute("href");

            $groupURL = env("BASE_URL") . "/ajax" . $href . "&_ts=" . $now;
            $this->rawURLs[] = $groupURL;

            $this->crawlCommand->info("Added " . $groupURL . " to rawUrls");
        }

        $this->crawlCommand->info("Prepare URLs done");
    }

    /**
     * Foreach prased URL get all matches and try to construct Match
     * and insert into DB
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

                    // do we have this unique_id already in DB?
                    if (MatchService::alreadyExists($match->unique_id)) {
                        // dont parse
                        $this->crawlCommand->info("Parsed but SKIPPED game because it already exists " . $match->unique_id);
                        break;
                    }

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
                    // remove more than 2 spaces
                    $match->unique_name = preg_replace("/\s\s+/", "", $match->unique_name);
                    $match->unique_name = preg_replace("/\s/", "_", $match->unique_name);

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

                    $match->save();

                    $this->crawlCommand->info("Parsed and added game " . $match->unique_id);
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