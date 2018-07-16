<?php

namespace App\Http\Controllers;

use Aginev\Datagrid\Datagrid;
use App\Match;
use App\MatchBet;
use Illuminate\Http\Request;

class MatchesController extends Controller
{

    public function showMatches(Request $request) {

        $grid = $this->getMatchesGrid();

        return view("matches.show",[
            "grid" => $grid
        ]);
    }

    private function getMatchesGrid() {

        $matches = Match::all()->sortByDesc("date_of_game");

        $request = app("request");

        $grid = new Datagrid($matches, $request->get('f', []));

        $grid->setColumn("id" , "ID");

        $grid->setColumn("teama", "Team A");
        $grid->setColumn("teamb", "Team B");

        $grid->setColumn("sport", "Sport");
        $grid->setColumn("type", "Type");
        $grid->setColumn("category", "Category");

        $grid->setColumn("matchbets", "Match Bets", [
            "wrapper" => function ($value, $row) {
                $matchBets = MatchBet::where("match_id", $row->id);
                return view("matches.matchbets", [
                    "matchBets" => $matchBets
                ]);
            }
        ]);

        $grid->setColumn("result", "Result");

        $grid->setColumn("date_of_game", "Game time");

        return $grid;
    }

}
