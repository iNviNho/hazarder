<?php

namespace App\Http\Controllers;

use Aginev\Datagrid\Datagrid;
use App\Ticket;
use App\UserLog;
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

class LogsController extends Controller
{

    public function showMyLogs() {

        $grid = $this->getMyLogsGrid();

        return view("logs.show", [
            "grid" => $grid
        ]);
    }

    private function getMyLogsGrid() {

        $tickets = UserLog::query();

        $columns = [
            # simple results numbering, not related to table PK or any obtained data
            (new FieldConfig())
                ->setName('id')
                ->setLabel('ID')
                ->setSortable(true)
                ->setSorting("desc"),
            (new FieldConfig())
                ->setName('info')
                ->setLabel('Log'),
            (new FieldConfig())
                ->setName('user_ticket_id')
                ->setLabel('User ticket match')
                ->setCallback(function ($val, $row) {
                    if (!is_null($row->getSrc()->user_ticket_id)) {
                        $userTicket = UserTicket::find($row->getSrc()->user_ticket_id);
                        $alink = "<a href='/match/" . $userTicket->ticket->match->id . "' >" . $userTicket->ticket->match->name . "</a";
                        return $alink;
                    } else {
                        return "-";
                    }
                }),
            (new FieldConfig())
                ->setName('user_ticket_id')
                ->setLabel('User ticket ID'),
            (new FieldConfig())
                ->setName('created_at')
                ->setLabel('Created at')
                ->setCallback(function ($val, $row) {
                    return $row->getSrc()->created_at->format("d.m.Y H:i");
                })
        ];

        # Instantiate & Configure Grid
        $datagrid = new Grid(
            (new GridConfig())
                ->setName('logs_datagrid')
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
