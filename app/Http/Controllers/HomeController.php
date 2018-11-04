<?php

namespace App\Http\Controllers;

use App\BettingProvider;
use App\Events\UserLogEvent;
use App\MarcingaleUserRound;
use App\Settings;
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
    public function index(Request $request)
    {
        $user = Auth::user();

        $from = $request->get("from");
        $name = $request->get("name");

        if (is_null($from)) {
            $from = Carbon::now()->subDays(7)->setTime(0, 0)->getTimestamp();
        }
        if (is_null($name)) {
            $name = "this-week";
        }

        $to = Carbon::now();
        $from = Carbon::createFromTimestamp($from);
        $someYearsAgo = Carbon::now()->subYears(10);

        /****************
         * RIGHT PANEL DATA
         ****************/
        $gameType = "%marcingale%";
        $betTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "LIKE", $gameType);
            })
            ->whereBetween("created_at", [$from, $to])
            ->count();

        $wonTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "LIKE", $gameType);
            })
            ->where("bet_win", "=", "1")
            ->whereBetween("created_at", [$from, $to])
            ->count();

        $lostTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "LIKE", $gameType);
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
                $q->where('game_type', "LIKE", $gameType);
            })
            ->whereBetween("created_at", [$from, $to])
            ->sum("bet_amount");
        $sumOfWonAmounts = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType) {
                $q->where('game_type', "LIKE", $gameType);
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

        /****************
         * TOP PANEL DATA SHOWING ALL BETTING PROVIDERS INFORMATIOn
         ****************/
        $bettingProvidersInformation = [];
        foreach (BettingProvider::all() as $bP) {

            if (!Settings::isBettingProviderEnabled(Auth::user()->id, $bP->id)) {
                continue;
            }

            $information = [];

            $currentBetAmount = UserTicket::selectRaw('sum(bet_amount) as bet_amount')
                ->where([
                    "user_id" => $user->id,
                    "status" => "bet"
                ])
                ->whereHas('Ticket', function ($q) use($bP) {
                    $q->whereHas("Match", function ($q) use($bP) {
                        $q->where("betting_provider_id","=", $bP->id);
                    });
                })
                ->pluck("bet_amount");
            $information["currentBetAmount"] = $currentBetAmount[0] == null ? "0.00" : $currentBetAmount[0];

            $currentBetPossibleWin = UserTicket::selectRaw('sum(bet_possible_win) as bet_possible_win')
                ->where([
                    "user_id" => $user->id,
                    "status" => "bet"
                ])
                ->whereHas('Ticket', function ($q) use($bP) {
                    $q->whereHas("Match", function ($q) use($bP) {
                        $q->where("betting_provider_id","=", $bP->id);
                    });
                })
                ->pluck("bet_possible_win");
            $information["currentBetPossibleWin"] = $currentBetPossibleWin[0] == null ? "0.00" : $currentBetPossibleWin[0];

            $currentBetPossibleClearWin = UserTicket::selectRaw('sum(bet_possible_clear_win) as bet_possible_clear_win')
                ->where([
                    "user_id" => $user->id,
                    "status" => "bet"
                ])
                ->whereHas('Ticket', function ($q) use($bP) {
                    $q->whereHas("Match", function ($q) use($bP) {
                        $q->where("betting_provider_id","=", $bP->id);
                    });
                })
                ->pluck("bet_possible_clear_win");
            $information["currentBetPossibleClearWin"] = $currentBetPossibleClearWin[0] == null ? "0.00" : $currentBetPossibleClearWin[0];


            $bettingProvidersInformation[$bP->id] = $information;
        }


        /****************
         * CHART TOP NUMBER #1
         ****************/
        $betTicketsChartData = [];
        $betTicketsChartLabel = [];
        foreach (BettingProvider::all() as $bP) {

            if (!Settings::isBettingProviderEnabled(Auth::user()->id, $bP->id)) {
                continue;
            }

            $sumOfBetAmountsUntilFrom = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType, $bP) {
                    $q->where('game_type', "LIKE", $gameType);
                    $q->whereHas("Match", function ($q) use($bP) {
                        $q->where("betting_provider_id","=", $bP->id);
                    });
                })
                ->whereBetween("created_at", [$someYearsAgo, $from])
                ->sum("bet_amount");
            $sumOfWonAmountsUntilFrom = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType, $bP) {
                    $q->where('game_type', "LIKE", $gameType);
                    $q->whereHas("Match", function ($q) use($bP) {
                        $q->where("betting_provider_id","=", $bP->id);
                    });
                })
                ->where("bet_win", "=", "1")
                ->whereBetween("created_at", [$someYearsAgo, $from])
                ->sum("bet_possible_win");
            $profitUntilFrom = bcsub($sumOfWonAmountsUntilFrom, $sumOfBetAmountsUntilFrom, 2);

            // chart that will show money over time
            $betTicketsForChartData = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType, $bP) {
                    $q->where('game_type', "LIKE", $gameType);
                    $q->whereHas("Match", function ($q) use($bP) {
                        $q->where("betting_provider_id","=", $bP->id);
                    });
                })
                ->whereBetween("created_at", [$from, $to])
                ->orderBy("created_at", "asc")
                ->get();
            $amount = $profitUntilFrom;
            $betTicketsBPData = [];
            foreach ($betTicketsForChartData as $id => $betTicketChart) {

                if ($betTicketChart->bet_win > 0) {
                    $amount = BC::add($amount, $betTicketChart->bet_possible_clear_win,2);
                } else {
                    $amount = BC::sub($amount, $betTicketChart->bet_amount,2);
                }


                $timestamp = $betTicketChart->created_at->getTimestamp();

                // does this key already exists?
//                while(array_key_exists($timestamp, $betTicketsBPData)) {
//                     if it exist, add one second to timestamp
//                    $timestamp++;
//                }
                $betTicketsBPData[$timestamp] = $amount;
                $betTicketsChartLabel[$timestamp] = true;
            }

            $betTicketsChartData[$bP->id] = $betTicketsBPData;
        }


        $betTicketsChartLabel = array_keys($betTicketsChartLabel);
        sort($betTicketsChartLabel);


        // prepare all chart data
        $sumOfBetAmountsUntilFrom = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType, $bP) {
                $q->where('game_type', "LIKE", $gameType);
            })
            ->whereBetween("created_at", [$someYearsAgo, $from])
            ->sum("bet_amount");
        $sumOfWonAmountsUntilFrom = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType, $bP) {
                $q->where('game_type', "LIKE", $gameType);
            })
            ->where("bet_win", "=", "1")
            ->whereBetween("created_at", [$someYearsAgo, $from])
            ->sum("bet_possible_win");
        $profitUntilFrom = bcsub($sumOfWonAmountsUntilFrom, $sumOfBetAmountsUntilFrom, 2);

        // chart that will show money over time
        $betTicketsForChartData = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->whereHas('Ticket', function($q) use($gameType, $bP) {
                $q->where('game_type', "LIKE", $gameType);
            })
            ->whereBetween("created_at", [$from, $to])
            ->orderBy("created_at", "asc")
            ->get();
        $amount = $profitUntilFrom;
        $allChartData = [];
        foreach ($betTicketsForChartData as $id => $betTicketChart) {

            if ($betTicketChart->bet_win > 0) {
                $amount = BC::add($amount, $betTicketChart->bet_possible_clear_win,2);
            } else {
                $amount = BC::sub($amount, $betTicketChart->bet_amount,2);
            }


            $timestamp = $betTicketChart->created_at->getTimestamp();

            $allChartData[$timestamp] = $amount;
        }



        return view("home", [
            "bettingProvidersInformation" => $bettingProvidersInformation,
            "betTicketsChartLabel" => $betTicketsChartLabel,
            "betTicketsChartData" => $betTicketsChartData,
            "allChartData" => $allChartData,
            "data" => $data,
            "name" => $name,
            "marcingaleRounds" => $marcingaleRounds,
            "marcingaleOpenRounds" => $marcingaleOpenRounds,
            "marcingaleWonRounds" => $marcingaleWonRounds,
            "marcingaleLostRounds" => $marcingaleLostRounds
        ]);
    }
}
