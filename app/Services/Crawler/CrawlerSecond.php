<?php
/**
 * Created by PhpStorm.
 * User: vladino
 * Date: 15.05.18
 * Time: 21:49
 */

namespace App\Services\Crawler;

use App\BettingProvider;
use App\Match;
use App\MatchBet;
use App\Services\AppSettings;
use App\Services\Match\MatchService;
use BCMathExtended\BC;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class CrawlerSecond implements Crawlable
{

    private $crawlCommand;
    private $guzzleClient;

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
    public function crawl() {

        $this->crawlCommand->info("Crawling started ...");

        // crawl
        if ($this->isEnabled()) {
            $this->parseAndPersistMatches();
        }
    }

    /**
     * Is this provider enabled?
     * @return bool
     */
    public function isEnabled()
    {
        return BettingProvider::isEnabled(BettingProvider::SECOND_PROVIDER_N);
    }

    public function parseAndPersistMatches() {

        $isFirst = true;
        $order = 0;
        $now = Carbon::now();
        while (true) {

            $url = env("SECOND_BETTING_PROVIDER_CRAWL_URL") . $now->format("Y-m-d");

            if (!$isFirst) {
                $url .= "&order=".$order;
            } else {
                // every other bet will not be first
                $isFirst = false;
            }

            $this->crawlCommand->info("Parsing " . $url);
            $result = $this->guzzleClient->get($url);
            $content = json_decode($result->getBody()->getContents());

            // if we don't have any new bets, lets end it
            if (count($content->bets) < 1) {
                break;
            }

            $order = $content->maxBoxOrder;

            // otherwise process it
            $this->processContent($content);
        }

    }

    private function processContent($content) {

        // prepare boxes
        $boxes = [];
        foreach ($content->boxes as $box) {
            $boxes[$box->boxId] = $box;
        }

        foreach ($content->bets as $bet) {

            $now = Carbon::now();

            // take only bet that belongs to 1 boxIds
            if (count($bet->boxIds) != 1) {
                continue;
            }

            // it has to be marked with "bi"
            if (strpos($bet->boxIds[0], "bi") === false) {
                continue;
            }

            $betBox = $boxes[$bet->boxIds[0]];

            // lets create a match
            $match = new Match();

            $match->betting_provider = BettingProvider::SECOND_PROVIDER_N;

            $match->category = $betBox->name;

            $match->name = $bet->participantOrder;
            $match->unique_id = $bet->betId;
            $match->number = $bet->betNumber;

            // define matchbetsCount
            $matchBetsCount = 0;
            foreach ($bet->selections as $selection) {
                if ($selection->type == "selection") {
                    $matchBetsCount += 1;
                }
            }

            // define match type
            if ($matchBetsCount == 6 || $matchBetsCount == 5) {
                $match->type = "normal";
            }
            if ($matchBetsCount == 3) {
                $match->type = "simple";
            }
            if ($matchBetsCount == 2) {
                $match->type = "goldengame";
            }

            // for not supported match type, just skip
            if ($match->type == null) {
                continue;
            }

            // does this match already exists
            if (MatchService::alreadyExists($match->unique_id)) {
                // dont parse again, please just update rates of matchbets
                $this->updateMatch($match, $bet);
                continue;
            }

            if (count($bet->participants) == 2) {
                $match->teama = $bet->participants[0];
                $match->teamb = $bet->participants[1];
            } else {
                // more participants than we support, skip
                continue;
            }
            $match->date_of_game = Carbon::createFromTimeString($bet->expirationTime)->addHours(2);
            $match->unique_name = $bet->participantOrder . $bet->betId;
            $match->sport = mb_strtolower($betBox->subtitle);

            $match->save();

            // lets setup matchbets
            foreach ($bet->selections as $selection) {

                // don't parse selection that is not marked as type selection
                if ($selection->type != "selection") {
                    continue;
                }

                $matchBet = new MatchBet();
                if (!$matchBet->setName($selection->tip)) {
                    // don't parse
                    continue;
                }
                $matchBet->value = $selection->odds;

                $matchBet->datainfo = $selection->tip;

                $match->getMatchBets()->save($matchBet);
            }

            $match->created_at = $now;
            $match->updated_at = $now;

            $match->save();

            $this->crawlCommand->info("Parsed and added game " . $match->unique_id);
        }


    }


    public function updateMatch($match, $bet) {

        $ourMatch = Match::where("unique_id", "=", $match->unique_id)->first();

        // define matchbetsCount
        $matchBetsCount = 0;
        foreach ($bet->selections as $selection) {
            if ($selection->type == "selection") {
                $matchBetsCount += 1;
            }
        }

        // check if count of our matchbets is the same as currently online
        // if it is, go throught all and update base on name
        if ($matchBetsCount == $ourMatch->getMatchBets()->count()) {
            foreach ($bet->selections as $selection) {

                // don't parse selection that is not marked as type selection
                if ($selection->type != "selection") {
                    continue;
                }

                // lets get OUR matchbet
                $matchBet = MatchBet::where([
                    "match_id" => $ourMatch->id,
                    "datainfo" => $selection->tip
                ])->first();

                // if odd was changed
                if (BC::comp($matchBet->value, $selection->odds) != 0) {

                    $matchBet->value = $selection->odds;
                    $matchBet->updated_at = Carbon::now();

                    $matchBet->save();
                }
            }

            // run update ticket for this match
            $ourMatch->afterMatchBetUpdate($this->crawlCommand);

            // done, log
            $this->crawlCommand->info("Updated matchbets for already existing match " . $match->unique_id);

        } else {
            $this->crawlCommand->info("NOT Updated match because our match has " . $ourMatch->getMatchBets()->count() . " bets
                and crawler gave us " . count($bet->selections) . " for already existing match " . $match->unique_id);
        }

    }





}