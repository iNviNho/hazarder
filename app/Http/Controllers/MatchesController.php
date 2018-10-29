<?php

namespace App\Http\Controllers;

use App\Events\UserLogEvent;
use App\MarcingaleUserRound;
use App\MarcingaleUserTicket;
use App\Match;
use App\MatchBet;
use App\Ticket;
use App\UserTicket;
use BCMathExtended\BC;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Nayjest\Grids\Components\ColumnHeadersRow;
use Nayjest\Grids\Components\FiltersRow;
use Nayjest\Grids\Components\HtmlTag;
use Nayjest\Grids\Components\OneCellRow;
use Nayjest\Grids\Components\RenderFunc;
use Nayjest\Grids\Components\ShowingRecords;
use Nayjest\Grids\Components\TFoot;
use Nayjest\Grids\Components\THead;
use Nayjest\Grids\EloquentDataProvider;
use Nayjest\Grids\FieldConfig;
use Nayjest\Grids\Grid;
use Nayjest\Grids\GridConfig;
use Nayjest\Grids\SelectFilterConfig;

class MatchesController extends Controller
{

    public function showMatches(Request $request) {

        $grid = $this->getMatchesGrid();

        return view("matches.show",[
            "grid" => $grid
        ]);
    }

    public function showMatch($matchID) {

        $match = Match::where("id", "=", $matchID)->first();

        $marcingaleUserRounds = MarcingaleUserRound::where([
            "user_id" => Auth::user()->id,
            "status" => "failed"
        ]);

        return view("match.show",[
            "match" => $match,
            "marcingaleUserRounds" => $marcingaleUserRounds
        ]);
    }

    private function getMatchesGrid() {

        $matches = Match::where('date_of_game', '>=', Carbon::now()->format("Y-m-d H:i:s"));

        $sports = Match::selectRaw("sport")
            ->groupBy("sport")
            ->pluck("sport", "sport")
            ->toArray();

        $categories = Match::selectRaw("category")
            ->groupBy("category")
            ->pluck("category", "category")
            ->toArray();

        $types = Match::selectRaw("type")
            ->groupBy("type")
            ->pluck("type", "type")
            ->toArray();

        $columns = [
            # simple results numbering, not related to table PK or any obtained data
            (new FieldConfig())
                ->setName('id')
                ->setLabel('ID')
                ->setSortable(true)
                ->setSorting("desc"),
            (new FieldConfig())
                ->setName('teama')
                ->setLabel('TeamA')
                ->setSortable(true),
            (new FieldConfig())
                    ->setName('teamb')
                    ->setLabel('TeamB')
                    ->setSortable(true),
            (new FieldConfig())
                    ->setName('sport')
                    ->setLabel('Sport')
                    ->setSortable(true)
                    ->addFilter(
                        (new SelectFilterConfig())
                            ->setName('sport')
                            ->setOptions($sports)
                    ),
            (new FieldConfig())
                    ->setName('type')
                    ->setLabel('Type')
                    ->setSortable(true)
                    ->addFilter(
                        (new SelectFilterConfig())
                            ->setName('type')
                            ->setOptions($types)
                    ),
            (new FieldConfig())
                ->setName('category')
                ->setLabel('Category')
                ->setSortable(true)
                ->addFilter(
                    (new SelectFilterConfig())
                        ->setName('category')
                        ->setOptions($categories)
                ),
            (new FieldConfig)
                ->setName("id")
                ->setLabel('Matchbets')
                ->setCallback(function ($val, $row) {
                    $matchBets = MatchBet::where("match_id", $val);
                    return view("matches.matchbets", [
                        "matchBets" => $matchBets
                    ]);
                })
            ,
            (new FieldConfig())
                ->setName('date_of_game')
                ->setLabel('Gametime')
                ->setSortable(true),
            (new FieldConfig())
                ->setName('id')
                ->setLabel('Action')
                ->setCallback(function ($val, $row) {
                    return "<a href='/match/".$row->getSrc()->id . "' class='btn btn-primary'>GAME</a>";
                }),
        ];

        # Instantiate & Configure Grid
        $datagrid = new Grid(
            (new GridConfig())
                ->setName('matches_datagrid')
                ->setDataProvider(new EloquentDataProvider($matches))
                ->setCachingTime(0)
                ->setColumns($columns)
                ->setComponents([
                    (new THead())
                        ->setComponents([
                            new ColumnHeadersRow(),
                            new FiltersRow(),

                        ])
                        ->addComponent(
                            (new HtmlTag)
                                ->setTagName('button')
                                ->setAttributes([
                                    'type' => 'submit',
                                    'class' => 'btn btn-success btn-small float-right'
                                ])
                                ->addComponent(new RenderFunc(function() {
                                    return '<i class="glyphicon glyphicon-refresh"></i> Filter';
                                }))
                        )
                    ,
                    (new TFoot())
                        ->addComponent(
                            (new OneCellRow)
                                ->addComponent(new ShowingRecords())
                        )
                ])
        );

        return $datagrid;
    }

    public function continueMarcingaleRound(Request $request) {

        $match = Match::find($request->get("match-id"));
        $matchBet = MatchBet::where([
            "name" => $request->get("bet-option"),
            "match_id" => $match->id
        ])->first();
        $marcingaleTicketRound = MarcingaleUserRound::find($request->get("marcingale-round"));

        $ticket = Ticket::createAndInsertTicket($match, $matchBet, "prepared", "tobeplayed", "marcingale-custom", true);

        $userTicket = new UserTicket();
        $userTicket->status = "approved";
        $userTicket->external_ticket_id = "-";
        $userTicket->user_id = Auth::user()->id;
        $userTicket->ticket_id = $ticket->id;

        $userTicket->bet_option = $ticket->matchbet->name;

            $doneLevels = 0;
            foreach ($marcingaleTicketRound->getMarcingaleUserTickets()->get() as $marTicket) {
                if ($marTicket->userTicket()->first()->status != "canceled") {
                    $doneLevels++;
                }
            }

            $marcingaleUserTicket = new MarcingaleUserTicket();
            $marcingaleUserTicket->user_id = Auth::user()->id;
            $marcingaleUserTicket->level = $doneLevels + 1;
            $marcingaleUserTicket->marcingale_user_round_id = $marcingaleTicketRound->id;

        $userTicket->bet_amount = $request->get("bet-amount");

        $userTicket->bet_rate = $ticket->matchbet->value;
        $userTicket->bet_possible_win = BC::mul($userTicket->bet_amount, $userTicket->bet_rate, 3);
        $userTicket->bet_possible_win = BC::roundUp($userTicket->bet_possible_win, 2);

        $userTicket->bet_possible_clear_win = bcsub($userTicket->bet_possible_win, $userTicket->bet_amount, "2");

        $userTicket->bet_win = 0; // default we always obviously won 0 so far

        $userTicket->save();

        // dont forget to set user_ticket_id for marcingale user tickets
        $marcingaleUserTicket->user_ticket_id = $userTicket->id;
        $marcingaleUserTicket->save();

        event(new UserLogEvent("UserTicket for game type: " . $ticket->game_type . " with ID: " . $userTicket->id . " created.", Auth::user()->id, $userTicket->id));

        // lets bet
        $userTicket->bet();

        $marcingaleTicketRound->status = "open";
        $marcingaleTicketRound->save();

        return redirect("/my-tickets")->with("msg", "Match successfully bet");
    }

}
