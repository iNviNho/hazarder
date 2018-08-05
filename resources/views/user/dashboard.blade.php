@extends('layouts.app')

@section('content')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>

    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="basic-content">
                    <h1>Dashboard</h1>

                    @foreach ($gameTypeData as $gameTypeName => $gameType)
                    <div class="game_type{{$gameTypeName}}">
                        <div class="game_type_title col-md-2">
                            Game type: <span>{{strtoupper($gameTypeName)}}</span>
                            <br class="clear">
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="myChart{{$gameTypeName}}"></canvas>
                            </div>
                            <div class="col-md-4">
                                <h3>Statistics:</h3>
                                <table class="table table-hovered table-bordered table-striped">
                                    <tr>
                                        <td>Bet tickets</td><td><strong>{{$gameType["bet_tickets"]}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Won tickets</td><td><strong>{{$gameType["won_tickets"]}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Lost tickets</td><td><strong>{{$gameType["lost_tickets"]}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Profit</td><td><strong>{{$gameType["profit"]}}</strong></td>
                                    </tr>

                                </table>
                            </div>
                        </div>
                    </div>

                    <hr>
                    @endforeach

                </div>

            </div>
        </div>
    </div>

    <script>
        $(function() {

            @foreach ($gameTypeData as $gameTypeName => $gameType)

            var ctx = document.getElementById('myChart{{$gameTypeName}}').getContext('2d');

            data = {
                datasets: [{
                    data: [{{$gameType["won_tickets"]}}, {{$gameType["lost_tickets"]}}],
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
            @endforeach


        });

    </script>
@endsection
