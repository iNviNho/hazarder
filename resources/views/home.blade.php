@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>

<div class="container my-container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="basic-content">
                <div class="row">
                    <h1 class="col-lg-9 col-md-6">
                        Welcome {{Auth::user()->name}}!
                        <p style="font-size: 18px; padding-top: 10px;">Dashboard only for <strong>Marcingale</strong></p>
                    </h1>
                    <div class="col-lg-3 col-md-6">
                        <ul class="list-group text-right dashboardul">
                            <li class="list-group-item list-group-item-action @if ($name == "today") active @endif"><a href="/home/today">TODAY</a></li>
                            <li class="list-group-item list-group-item-action @if ($name == null) active @endif"><a href="/home">THIS WEEK</a></li>
                            <li class="list-group-item list-group-item-action @if ($name == "month") active @endif"><a href="/home/month">THIS MONTH</a></li>
                            <li class="list-group-item list-group-item-action @if ($name == "year") active @endif"><a href="/home/year">THIS CURRENT YEAR</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">

                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item active"><h3>Current Credit</h3></li>
                                    <li class="list-group-item"><h3>{{Auth::user()->credit}}€</h3></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item active"><h3>Current Bet Amount</h3></li>
                                    <li class="list-group-item"><h3>{{$currentBetAmount}}€</h3></li>
                                </ul>
                            </div>

                            <div class="col-md-6" style="padding-top: 40px;">
                                <ul class="list-group">
                                    <li class="list-group-item active"><h3>Current Bet Possible Win</h3></li>
                                    <li class="list-group-item"><h3>{{$currentBetPossibleWin}}€</h3></li>
                                </ul>
                            </div>
                            <div class="col-md-6" style="padding-top: 40px;">
                                <ul class="list-group">
                                    <li class="list-group-item active"><h3>Current Bet Possible Clear Win</h3></li>
                                    <li class="list-group-item"><h3>{{$currentBetPossibleClearWin}}€</h3></li>
                                </ul>
                            </div>
                        </div>

                        <h3 style="padding-top: 15px;">Profit by the time</h3>
                        <canvas id="myChartA"></canvas>

                        <h3 style="padding-top: 15px;">Marcingale round level finished by the time</h3>
                        <canvas id="myChartB"></canvas>

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
                        <a href="/dashboard/marcingale/detailed" class="btn btn-primary">MARCINGALE DETAILED</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>

    var configa = {
        type: 'line',
        data: {
            labels: [
                @foreach ($betTicketsChartData as $i => $value)
                    "{{$i}}",
                @endforeach
            ],
            datasets: [{
                label: 'Profit by the time',
                borderColor: "red",
                data: @php echo json_encode(array_values($betTicketsChartData)) @endphp,
                fill: false,
            },]
        },
        options: {
            responsive: true,
            hover: {
                mode: 'nearest',
                intersect: true
            },
            pointHoverBorderWidth: 0
        }
    };

    var configb = {
        type: 'line',
        data: {
            labels: [
                @foreach ($marcingaleRoundsChartData as $i => $value)
                    "{{$i}}",
                @endforeach
            ],
            datasets: [{
                label: 'Marcingale round level finished by the time',
                borderColor: "skyblue",
                data: @php echo json_encode(array_values($marcingaleRoundsChartData)) @endphp,
                fill: false,
            },]
        },
        options: {
            responsive: true,
            hover: {
                mode: 'nearest',
                intersect: true
            },
            pointHoverBorderWidth: 0
        }
    };

    window.onload = function() {
        var ctxa = document.getElementById('myChartA').getContext('2d');
        window.myLine = new Chart(ctxa, configa);

        var ctxb = document.getElementById('myChartB').getContext('2d');
        window.myLine = new Chart(ctxb, configb);
    };

</script>
@endsection
