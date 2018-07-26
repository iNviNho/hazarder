<?php

namespace App\Http\Controllers;

use Aginev\Datagrid\Datagrid;
use App\Ticket;
use App\UserTicket;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{

    public function showTickets() {

        $grid = $this->getTicketsGrid();

        return view("tickets.show", [
            "grid" => $grid
        ]);
    }

    private function getTicketsGrid() {

        $tickets = Ticket::orderBy('id', "desc")->get();

        $request = app("request");

        $grid = new Datagrid($tickets, $request->get('f', []));

        $grid->setColumn("id" , "ID");
        $grid->setColumn("match_name", "Match", [
            "wrapper" => function($value, $row) {
                return $row->match->name;
            }
        ]);
        $grid->setColumn("match_category", "Category", [
            "wrapper" => function($value, $row) {
                return $row->match->category;
            }
        ]);


        $grid->setColumn('status', 'Status');
        $grid->setColumn("game_type", "Game type");

        $grid->setColumn("bet_option", "Bet option", [
            "wrapper" => function($value, $row) {
                return $row->matchbet->name;
            }
        ]);
//        $grid->setColumn("bet_amount", "Bet amount");
        $grid->setColumn("bet_rate", "Rate", [
            "wrapper" => function($value, $row) {
                return $row->matchbet->value;
            }
        ]);
//        $grid->setColumn("bet_possible_win", "Bet possible win");
//        $grid->setColumn("bet_possible_clear_win", "Bet possible clear win");
//        $grid->setColumn("date_of_game", "Date of game", [
//            "wrapper" => function($value, $row) {
//                return $row->match->date_of_game;
//            }
//        ]);
//        $grid->setColumn("result", "RESULT", [
//            "wrapper" => function ($value, $row) {
//                $value = $row->bet_win;
//                if ($value == -1) {
//                    return "<span class='glyphicon glyphicon-remove'></span>";
//                }
//                if ($value == "0") {
//
//                    // if was not bet, dont show anything
//                    if ($row->status == "bet") {
//                        return "<span class='glyphicon glyphicon-time'></span>";
//                    } else {
//                        return "-";
//                    }
//                }
//                if ($value == "1") {
//                    return "<span class='glyphicon glyphicon-ok'></span>";
//                }
//            }
//        ]);

//        $grid->setColumn("actions", "Actions", [
//            "wrapper" => function ($value, $row) {
//                return view("tickets.buttons", [
//                    "ticket" => $row
//                ]);
//            }
//        ]);

        return $grid;
    }

//    public function approve($ticketID) {
//
//        $ticket = Ticket::find($ticketID);
//        $ticket->status = "approved";
//
//        $ticket->save();
//
//        return redirect()->action('TicketController@showTickets');
//    }
//
//    public function disapprove($ticketID) {
//
//        $ticket = Ticket::find($ticketID);
//        $ticket->status = "disapproved";
//
//        $ticket->save();
//
//        return redirect()->action('TicketController@showTickets');
//    }
//
//    public function bet($ticketID) {
//
//        $ticket = Ticket::find($ticketID);
//        $ticket->bet();
//
//        return redirect()->action('TicketController@showTickets');
//    }
//
//    public function checkresult($ticketID) {
//
//        $ticket = Ticket::find($ticketID);
//        Ticket::tryToCheckResult($ticket);
//
//        return redirect()->action('TicketController@showTickets');
//    }

    public function showMyTickets() {

        $grid = $this->getMyTicketsGrid();

        return view("tickets.show", [
            "grid" => $grid
        ]);
    }

    private function getMyTicketsGrid() {

        $user = Auth::user();
        $tickets = UserTicket::where("user_id", "=", $user->id)
            ->orderBy('id', "desc")
            ->get();

        $request = app("request");

        $grid = new Datagrid($tickets, $request->get('f', []));

        $grid->setColumn("id" , "ID");

        $grid->setColumn("match_name", "Match", [
            "wrapper" => function($value, $row) {
                return $row->ticket->match->name;
            }
        ]);
        $grid->setColumn("match_category", "Category", [
            "wrapper" => function($value, $row) {
                return $row->ticket->match->category;
            }
        ]);


        $grid->setColumn("game_type", "Game type", [
            "wrapper" => function($value, $row) {
                return $row->ticket->game_type;
            }
        ]);
        $grid->setColumn('status', 'Status');


        $grid->setColumn("bet_option", "Bet option");
        $grid->setColumn("bet_amount", "Bet amount");
        $grid->setColumn("bet_rate", "Rate");
        $grid->setColumn("bet_possible_win", "Bet possible win");
        $grid->setColumn("bet_possible_clear_win", "Bet possible clear win");
        $grid->setColumn("bet_win", "Bet win");
        $grid->setColumn("created_at", "Created", [
            "wrapper" => function($value, $row) {
                return $row->created_at;
            }
        ]);
        $grid->setColumn("result", "RESULT", [
            "wrapper" => function ($value, $row) {
                $value = $row->bet_win;
                if ($value == "-1") {
                    return "<span style='color: red;' class='fa fa-2x fa-sad-tear'></span>";
                }
                if ($value == "0") {

                    // if was not bet, dont show anything
                    if ($row->status == "bet") {
                        return "<span style='color: deepskyblue;' class='fa fa-2x fa-clock'></span>";
                    } else {
                        return "<span class='fa fa-2x fa-meh'></span>";
                    }
                }
                if ($value == "1") {
                    return "<span style='color: green;' class='fa fa-2x fa-check-circle'></span>";
                }
            }
        ]);

        return $grid;
    }

}
