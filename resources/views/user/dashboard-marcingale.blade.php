@extends('layouts.app')

@section('content')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>

    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="basic-content">
                    <div class="row">
                        <h1 class="col-lg-9 col-md-6">Marcingale detailed statistics</h1>
                        <div class="col-lg-3 col-md-6">
                            <ul class="list-group text-right dashboardul">
                                <li class="list-group-item list-group-item-action @if ($name == null) active @endif"><a href="/dashboard/marcingale/detailed">ALL</a></li>
                                <li class="list-group-item list-group-item-action @if ($name == "today") active @endif"><a href="/dashboard/marcingale/detailed/today">TODAY</a></li>
                                <li class="list-group-item list-group-item-action @if ($name == "yesterday") active @endif"><a href="/dashboard/marcingale/detailed/yesterday">YESTERDAY</a></li>
                                <li class="list-group-item list-group-item-action @if ($name == "last24hours") active @endif"><a href="/dashboard/marcingale/detailed/last24hours">LAST 24 HOURS</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="game_typemarcingale">
                        <div class="game_type_title col-md-2">
                            Game type: <span>MARCINGALE</span>
                            <br class="clear">
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="myChartmarcingale"></canvas>
                            </div>
                            <div class="col-md-4">
                                <h3>Statistics:</h3>
                                <table class="table table-hovered table-bordered table-striped">
                                    <tr>
                                        <td>Bet tickets</td><td><strong>{{$data["bet_tickets"]}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Won tickets</td><td><strong>{{$data["won_tickets"]}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Lost tickets</td><td><strong>{{$data["lost_tickets"]}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Win ratio</td><td><strong>{{$data["ratio"]}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Profit</td><td><strong>{{$data["profit"]}}</strong></td>
                                    </tr>
                                </table>
                                <h3>More:</h3>
                                <table class="table table-hovered table-bordered table-striped">
                                    <tr>
                                        <td>All rounds</td><td><strong>{{$marcingaleRounds}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Open rounds</td><td><strong>{{$marcingaleOpenRounds}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Won rounds</td><td><strong>{{$marcingaleWonRounds}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Lost rounds</td><td><strong>{{$marcingaleLostRounds}}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="col-md-12 marcingale-rounds-content">
                        <h1>Marcingale rounds</h1>

                            <div class="row marcingale-detailed-wrapper">
                                <div class="col-md-2 text-left"><strong>ID</strong></div>
                                <div class="col-md-1 text-center"><strong>Level</strong></div>
                                <div class="col-md-2 text-center"><strong>Tickets sum</strong></div>
                                <div class="col-md-2 text-center"><strong>Profit</strong></div>
                                <div class="col-md-2 text-right"><strong>Result</strong></div>
                                <div class="col-md-2 text-right"><strong>Created at</strong></div>
                                <div class="col-md-1 text-right"><strong>Action</strong></div>
                            </div>
                            @foreach($marcingaleRoundsGrip->get() as $round)

                            <div class="marcingale-round-wrapper">

                                <div class="row marcingale-detailed-wrapper
                                    @if ($round->status == "success") alert-success  @endif
                                    @if ($round->status == "failed") alert-danger  @endif
                                    @if ($round->status == "open") alert-info  @endif">
                                        <div class="col-md-2 text-left"><strong>{{$round->id}}</strong></div>
                                        <div class="col-md-1 text-center">{{$round->level_finished}}</div>
                                        <div class="col-md-2 text-center">{{$round->getMarcingaleUserTickets()->count()}}</div>
                                        <div class="col-md-2 text-center"><strong>{{$round->getProfit()}}€</strong></div>
                                        <div class="col-md-2 text-right">
                                            @if ($round->status == "success") <span style='color: green;' class='fa fa-2x fa-check-circle'></span> @endif
                                            @if ($round->status == "failed") <span style='color: red;' class='fa fa-2x fa-sad-tear'></span> @endif
                                            @if ($round->status == "open") <span style='color: deepskyblue;' class='fa fa-2x fa-clock'></span>  @endif
                                        </div>
                                        <div class="col-md-2 text-right">{{$round->created_at->format("d.m.Y H:i")}}</div>
                                        <div class="col-md-1 text-right">
                                            <span class="fa fa-chevron-down"></span>
                                        </div>

                                </div>

                                <div class="marcingale-detailed-tickets">
                                    <div class="row header">
                                        <div class="col-md-1"></div>
                                        <div class="col-md-2">Match</div>
                                        <div class="col-md-2 text-center">Bet amount</div>
                                        <div class="col-md-2 text-center">Bet rate</div>
                                        <div class="col-md-2 text-center">Bet possible win</div>
                                        <div class="col-md-2 text-center">Bet possible clear win</div>
                                        <div class="col-md-1">Result</div>
                                    </div>
                                    @foreach ($round->getMarcingaleUserTickets()->get() as $marUserTicket)
                                        @php
                                            $userTicket = $marUserTicket->userTicket()->first();
                                        @endphp
                                        @if ($userTicket->status == "canceled")
                                            @continue
                                        @endif
                                    <div class="row
                                        @if ($userTicket->bet_win == 1) alert-success  @endif
                                        @if ($userTicket->bet_win == -1) alert-danger  @endif
                                        @if ($userTicket->bet_win == 0) alert-info  @endif">
                                        <div class="col-md-1"></div>
                                        <div class="col-md-2">{{$userTicket->ticket->match->name}}</div>
                                        <div class="col-md-2 text-center">{{$userTicket->bet_amount}}</div>
                                        <div class="col-md-2 text-center">{{$userTicket->bet_rate}}</div>
                                        <div class="col-md-2 text-center">{{$userTicket->bet_possible_win}}</div>
                                        <div class="col-md-2 text-center">{{$userTicket->bet_possible_clear_win}}</div>
                                        <div class="col-md-1">
                                            @if ($userTicket->bet_win == 1) <span style='color: green;' class='fa fa-2x fa-check-circle'></span> @endif
                                            @if ($userTicket->bet_win == -1) <span style='color: red;' class='fa fa-2x fa-sad-tear'></span> @endif
                                            @if ($userTicket->bet_win == 0) <span style='color: deepskyblue;' class='fa fa-2x fa-clock'></span>  @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                            </div>
                            @endforeach
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        $(function() {


            var ctx = document.getElementById('myChartmarcingale').getContext('2d');

            data = {
                datasets: [{
                    data: [{{$data["won_tickets"]}}, {{$data["lost_tickets"]}}],
                    backgroundColor: ["green", "red"]
                }],

                // These labels appear in the legend and in the tooltips when hovering different arcs
                labels: [
                    'Won tickets',
                    'Lost tickets'
                ]
            };


            var doughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: data,
            });


            $(".marcingale-detailed-wrapper").on("click", function() {

                var dis = $(this);

                // if i click on this and it is open, just close
                if (dis.parent().find(".marcingale-detailed-tickets").css("display") == "block") {
                    dis.parent().find(".marcingale-detailed-tickets").slideUp();
                    return;
                }

                // hide everything
                $(".marcingale-detailed-tickets").slideUp();
                // show current
                dis.parent().find(".marcingale-detailed-tickets").slideToggle();
            });



        });

    </script>
@endsection
