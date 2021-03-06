<?php

namespace App\Http\Controllers;

use Aginev\Datagrid\Datagrid;
use App\Ticket;
use App\UserTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

class TicketController extends Controller
{

    public function showTickets() {

        $grid = $this->getTicketsGrid();

        return view("tickets.show", [
            "grid" => $grid
        ]);
    }

    private function getTicketsGrid() {

        $tickets = Ticket::where([
            "game_type" => "marcingale"
        ]);

        $columns = [
            # simple results numbering, not related to table PK or any obtained data
            (new FieldConfig())
                ->setName('id')
                ->setLabel('ID')
                ->setSortable(true)
                ->setSorting("desc"),
            (new FieldConfig())
                ->setName('match')
                ->setLabel('Match')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->match->name;
                }),
            (new FieldConfig())
                ->setName('category')
                ->setLabel('Category')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->match->category;
                }),
            (new FieldConfig())
                ->setName('status')
                ->setLabel('Status')
                ->setSortable(true),
            (new FieldConfig())
                ->setName('game_type')
                ->setLabel('Game type')
                ->setSortable(true),
            (new FieldConfig())
                ->setName('bet_option')
                ->setLabel('Bet option')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->matchbet->name;
                }),
            (new FieldConfig())
                ->setName('rate')
                ->setLabel('Rate')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->matchbet->value;
                }),
        ];

        # Instantiate & Configure Grid
        $datagrid = new Grid(
            (new GridConfig())
                ->setName('matches_datagrid')
                ->setDataProvider(new EloquentDataProvider($tickets))
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
                            (new OneCellRow())
                                ->addComponent(new ShowingRecords())
                        )
                ])
        );

        return $datagrid;
    }

    public function showMyTickets() {

        $grid = $this->getMyTicketsGrid();

        return view("tickets.myshow", [
            "grid" => $grid
        ]);
    }

    private function getMyTicketsGrid() {

        $user = Auth::user();
        $tickets = UserTicket::where("user_id", "=", $user->id)
            ->where("status", "!=", "canceled");

        $columns = [
            # simple results numbering, not related to table PK or any obtained data
            (new FieldConfig())
                ->setName('id')
                ->setLabel('ID')
                ->setSortable(true)
                ->setSorting("desc"),
            (new FieldConfig())
                ->setName('match')
                ->setLabel('Match')
                ->setCallback(function ($val, $row) {
                    $alink = "<a href='/match/" . $row->getSrc()->ticket->match->id . "' >" . $row->getSrc()->ticket->match->name . "</a>";
                    return $alink;
                }),
            (new FieldConfig())
                ->setName('match')
                ->setLabel('Link')
                ->setCallback(function ($val, $row) {
                    $alink = " <a href='" . $row->getSrc()->getLinkToBettingSite() . "' target='_blank'><img width='25px' src='images/logo.png'> </a> ";
                    return $alink;
                }),
            (new FieldConfig())
                ->setName('category')
                ->setLabel('Category')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->ticket->match->category;
                }),
            (new FieldConfig())
                ->setName('game_type')
                ->setLabel('Game type')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->ticket->game_type;
                }),
            (new FieldConfig())
                ->setName('status')
                ->setLabel('Status')
                ->setSortable(true),

            (new FieldConfig())->setName('bet_option')->setLabel('Bet option'),
            (new FieldConfig())->setName('bet_amount')->setLabel('Bet amount'),
            (new FieldConfig())->setName('bet_rate')->setLabel('Bet rate'),
            (new FieldConfig())->setName('bet_possible_win')->setLabel('Bet possible win'),
            (new FieldConfig())->setName('bet_possible_clear_win')->setLabel('Bet possible clear win'),

            (new FieldConfig())
                ->setName('gametime')
                ->setLabel('Gametime')
                ->setCallback(function ($val, $row) {
                    return Carbon::createFromTimeString($row->getSrc()->ticket->match->date_of_game)->format("d.m.Y H:i");
                }),
            (new FieldConfig())
                ->setName('created_at')
                ->setLabel('Created at')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->created_at->format("d.m.Y H:i");
                }),
            (new FieldConfig())
                ->setName('result')
                ->setLabel('Result')
                ->setCallback(function ($val, $row) {
                    $value = $row->getSrc()->bet_win;
                    if ($value == "-1") {
                        return "<span style='color: red;' class='fa fa-2x fa-sad-tear'></span>";
                    }
                    if ($value == "0") {

                        // if was not bet, dont show anything
                        if ($row->getSrc()->status == "bet") {
                            return "<span style='color: deepskyblue;' class='fa fa-2x fa-clock'></span>";
                        } else {
                            return "<span class='fa fa-2x fa-meh'></span>";
                        }
                    }
                    if ($value == "1") {
                        return "<span style='color: green;' class='fa fa-2x fa-check-circle'></span>";
                    }
                }),
        ];

        # Instantiate & Configure Grid
        $datagrid = new Grid(
            (new GridConfig())
                ->setName('matches_datagrid')
                ->setDataProvider(new EloquentDataProvider($tickets))
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
                            (new OneCellRow())
                                ->addComponent(new ShowingRecords())
                        )
                ])
        );

        return $datagrid;
    }

}
