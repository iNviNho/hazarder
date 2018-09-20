<?php

namespace App\Http\Controllers;

use App\MarcingaleUserRound;
use App\Ticket;
use App\UserTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $name = null)
    {
        $user = Auth::user();

        $gameTypeData = [];

        if ($name == "today") {
            $from = Carbon::now()->setTime(0,0);
            $to = Carbon::now();
        } elseif ($name == "yesterday") {
            $from = Carbon::now()->subDay(1)->setTime(0,0);
            $to = Carbon::now()->subDay(1)->setTime(23,59);
        } elseif ($name == "last24hours") {
            $from = Carbon::now()->subHours(24);
            $to = Carbon::now();
        } else {
            $from = Carbon::now()->subYear(10);
            $to = Carbon::now();
        }



        // ALL as GAME TYPE
        $betTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereBetween("created_at", [$from, $to])
            ->count();

        $wonTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->where("bet_win", "=", "1")
            ->whereBetween("created_at", [$from, $to])
            ->count();

        $lostTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->where("bet_win", "=", "-1")
            ->whereBetween("created_at", [$from, $to])
            ->count();

        // calculate ratio
        if ($betTickets == 0) {
            $ratio = "0.00";
        } else {
            $ratio = bcmul(bcdiv($wonTickets, $betTickets, 2), 100, 2);
        }

        // calculate profit
        // get how much we bet
        $sumOfBetAmounts = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereBetween("created_at", [$from, $to])
            ->sum("bet_amount");
        $sumOfWonAmounts = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->where("bet_win", "=", "1")
            ->whereBetween("created_at", [$from, $to])
            ->sum("bet_possible_win");
        $profit = bcsub($sumOfWonAmounts, $sumOfBetAmounts, 2);

        $data = [
            "bet_tickets" => $betTickets,
            "won_tickets" => $wonTickets,
            "lost_tickets" => $lostTickets,
            "ratio" => $ratio . " %",
            "profit" => $profit . " €"
        ];
        $gameTypeData["all"] = $data;

        // NOW DIVIDE INTO EACH GAME TYPE
        foreach (Ticket::$GAME_TYPES as $gameType) {

            $betTickets = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->whereBetween("created_at", [$from, $to])
                ->count();

            $wonTickets = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->where("bet_win", "=", "1")
                ->whereBetween("created_at", [$from, $to])
                ->count();

            $lostTickets = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->where("bet_win", "=", "-1")
                ->whereBetween("created_at", [$from, $to])
                ->count();

            // calculate ratio
            if ($betTickets == 0) {
                $ratio = "0.00";
            } else {
                $ratio = bcmul(bcdiv($wonTickets, $betTickets, 2), 100, 2);
            }


            // calculate profit
            // get how much we bet
            $sumOfBetAmounts = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->whereBetween("created_at", [$from, $to])
                ->sum("bet_amount");
            $sumOfWonAmounts = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->where("bet_win", "=", "1")
                ->whereBetween("created_at", [$from, $to])
                ->sum("bet_possible_win");
            $profit = bcsub($sumOfWonAmounts, $sumOfBetAmounts, 2);

            $data = [
                "bet_tickets" => $betTickets,
                "won_tickets" => $wonTickets,
                "lost_tickets" => $lostTickets,
                "ratio" => $ratio . " %",
                "profit" => $profit . " €"
            ];

            $gameTypeData[$gameType] = $data;
        }

        return view("user.dashboard", [
            "gameTypeData" => $gameTypeData,
            "name" => $name,
        ]);
    }

    public function marcingale(Request $request, $name = null) {

        $user = Auth::user();

        if ($name == "today") {
            $from = Carbon::now()->setTime(0,0);
            $to = Carbon::now();
        } elseif ($name == "yesterday") {
            $from = Carbon::now()->subDay(1)->setTime(0,0);
            $to = Carbon::now()->subDay(1)->setTime(23,59);
        } elseif ($name == "last24hours") {
            $from = Carbon::now()->subHours(24);
            $to = Carbon::now();
        } else {
            $from = Carbon::now()->subYear(10);
            $to = Carbon::now();
        }

        // NOW DIVIDE INTO EACH GAME TYPE
        $gameType = "marcingale";
        $betTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->whereBetween("created_at", [$from, $to])
            ->count();

        $wonTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->where("bet_win", "=", "1")
            ->whereBetween("created_at", [$from, $to])
            ->count();

        $lostTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->where("bet_win", "=", "-1")
            ->whereBetween("created_at", [$from, $to])
            ->count();

        // calculate ratio
        if ($betTickets == 0) {
            $ratio = "0.00";
        } else {
            $ratio = bcmul(bcdiv($wonTickets, $betTickets, 2), 100, 2);
        }


        // calculate profit
        // get how much we bet
        $sumOfBetAmounts = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->whereBetween("created_at", [$from, $to])
            ->sum("bet_amount");
        $sumOfWonAmounts = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->where("bet_win", "=", "1")
            ->whereBetween("created_at", [$from, $to])
            ->sum("bet_possible_win");
        $profit = bcsub($sumOfWonAmounts, $sumOfBetAmounts, 2);

        $data = [
            "bet_tickets" => $betTickets,
            "won_tickets" => $wonTickets,
            "lost_tickets" => $lostTickets,
            "ratio" => $ratio . " %",
            "profit" => $profit . " €"
        ];

        // more marcingale stats
        $marcingaleRounds = MarcingaleUserRound::where([
                "user_id" => $user->id
            ])
            ->whereBetween("created_at", [$from, $to])
            ->count();

        $marcingaleWonRounds = MarcingaleUserRound::where([
                "user_id" => $user->id,
                "status" => "success"
            ])
            ->whereBetween("created_at", [$from, $to])
            ->count();
        $marcingaleLostRounds = MarcingaleUserRound::where([
                "user_id" => $user->id,
                "status" => "failed"
            ])
            ->whereBetween("created_at", [$from, $to])
            ->count();
        $marcingaleOpenRounds  = MarcingaleUserRound::where([
                "user_id" => $user->id,
                "status" => "open"
            ])
            ->whereBetween("created_at", [$from, $to])
            ->count();


        // lets handle some weird stuff
        $marcingaleRoundsGrip = MarcingaleUserRound::where([
                "user_id" => $user->id
            ])
            ->whereBetween("created_at", [$from, $to])
            ->orderBy("created_at", "DESC");


        return view("user.dashboard-marcingale", [
            "data" => $data,
            "name" => $name,
            "marcingaleRoundsGrip" => $marcingaleRoundsGrip,
            "marcingaleRounds" => $marcingaleRounds,
            "marcingaleOpenRounds" => $marcingaleOpenRounds,
            "marcingaleWonRounds" => $marcingaleWonRounds,
            "marcingaleLostRounds" => $marcingaleLostRounds
        ]);

    }
}
