<?php

namespace App\Http\Controllers;

use App\Events\UserLogEvent;
use App\MarcingaleUserRound;
use App\UserTicket;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use BCMathExtended\BC;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function checkLogin() {
        if (Auth::check()) {
            return redirect("/home");
        } else {
            return redirect("/login");
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($name = null)
    {
        $user = Auth::user();

        $to = Carbon::now();
        if ($name == "today") {
            $from = Carbon::now()->setTime(0,0);
        } elseif ($name == "month") {
            $from = Carbon::now()->subMonth(1);
        } elseif ($name == "year") {
            $from = Carbon::now()->subYear(1);
        } else {
            $from = Carbon::now()->subDay(7)->setTime(0,0);
        }

        $someYearsAgo = Carbon::now()->subYears(10);

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

        // $currentBetAmount
        $currentBetAmount = UserTicket::selectRaw('sum(bet_amount) as bet_amount')
            ->where([
                "user_id" => $user->id,
                "status" => "bet"
            ])
            ->pluck("bet_amount");
        $currentBetPossibleWin = UserTicket::selectRaw('sum(bet_possible_win) as bet_possible_win')
            ->where([
                "user_id" => $user->id,
                "status" => "bet"
            ])
            ->pluck("bet_possible_win");
        $currentBetPossibleClearWin = UserTicket::selectRaw('sum(bet_possible_clear_win) as bet_possible_clear_win')
            ->where([
                "user_id" => $user->id,
                "status" => "bet"
            ])
            ->pluck("bet_possible_clear_win");

        // chart that will show money over time
        $sumOfBetAmountsUntilFrom = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->whereBetween("created_at", [$someYearsAgo, $from])
            ->sum("bet_amount");
        $sumOfWonAmountsUntilFrom = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->where("bet_win", "=", "1")
            ->whereBetween("created_at", [$someYearsAgo, $from])
            ->sum("bet_possible_win");
        $profitUntilFrom = bcsub($sumOfWonAmountsUntilFrom, $sumOfBetAmountsUntilFrom, 2);

        // chart that will show money over time
        $betTicketsForChartData = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "=", $gameType);
            })
            ->whereBetween("created_at", [$from, $to])
            ->orderBy("created_at", "asc")
            ->get();
        $betTicketsChartData = [];
        $amount = $profitUntilFrom;
        foreach ($betTicketsForChartData as $id => $betTicketChart) {

            if ($betTicketChart->bet_win > 0) {
                $amount = BC::add($amount, $betTicketChart->bet_possible_clear_win,2);
            } else {
                $amount = BC::sub($amount, $betTicketChart->bet_amount,2);
            }

            $betTicketsChartData[$id . " - " . $betTicketChart->created_at->format("d.m.Y")] = $amount;
        }



        // chart2
        $marcingaleRoundsForChartData = MarcingaleUserRound::where([
                "user_id" => $user->id
            ])
            ->where("status", "<>", "open")
            ->whereBetween("created_at", [$from, $to])
            ->get();
        $marcingaleRoundsChartData = [];
        foreach ($marcingaleRoundsForChartData as $id => $marcingaleRoundChart) {
            $marcingaleRoundsChartData[$id . " - " . $marcingaleRoundChart->created_at->format("d.m.Y")] = $marcingaleRoundChart->level_finished;
        }

        return view("home", [
            "currentBetAmount" => $currentBetAmount[0],
            "currentBetPossibleWin" => $currentBetPossibleWin[0],
            "currentBetPossibleClearWin" => $currentBetPossibleClearWin[0],
            "betTicketsChartData" => $betTicketsChartData,
            "marcingaleRoundsChartData" => $marcingaleRoundsChartData,
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
