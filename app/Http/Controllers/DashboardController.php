<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\UserTicket;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        $gameTypeData = [];

        // ALL as GAME TYPE
        $betTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->count();

        $wonTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->where("bet_win", "=", "1")
            ->count();

        $lostTickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->where("bet_win", "=", "-1")
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
            ->sum("bet_amount");
        $sumOfWonAmounts = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "=", "betanddone")
            ->where("bet_win", "=", "1")
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
                ->count();

            $wonTickets = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->where("bet_win", "=", "1")
                ->count();

            $lostTickets = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->where("bet_win", "=", "-1")
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
                ->sum("bet_amount");
            $sumOfWonAmounts = UserTicket::where("user_id", "=", $user->id)
                ->where("status", "=", "betanddone")
                ->whereHas('Ticket', function($q) use($gameType) {
                    $q->where('game_type', "=", $gameType);
                })
                ->where("bet_win", "=", "1")
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
            "gameTypeData" => $gameTypeData
        ]);
    }
}
