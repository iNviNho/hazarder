<?php

namespace App\Http\Controllers;

use App\Match;
use App\MatchBet;
use Illuminate\Http\Request;
use Nayjest\Grids\Components\ColumnHeadersRow;
use Nayjest\Grids\Components\FiltersRow;
use Nayjest\Grids\Components\OneCellRow;
use Nayjest\Grids\Components\ShowingRecords;
use Nayjest\Grids\Components\TFoot;
use Nayjest\Grids\Components\THead;
use Nayjest\Grids\EloquentDataProvider;
use Nayjest\Grids\FieldConfig;
use Nayjest\Grids\Grid;
use Nayjest\Grids\GridConfig;

class MatchesController extends Controller
{

    public function showMatches(Request $request) {

        $grid = $this->getMatchesGrid();

        return view("matches.show",[
            "grid" => $grid
        ]);
    }

    private function getMatchesGrid() {

        $matches = Match::query();

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
                    ->setSortable(true),
            (new FieldConfig())
                    ->setName('type')
                    ->setLabel('Type')
                    ->setSortable(true),
            (new FieldConfig())
                ->setName('category')
                ->setLabel('Category')
                ->setSortable(true),
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

}
