@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>

<div class="container my-container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-sm-12 col-xs-12">

            <div class="basic-content" style="margin-bottom: 40px;">
                <h3><span class="fas fa-smile-beam"></span> Welcome {{Auth::user()->name}}!</h3>
                <div class="row" style="margin-top: 40px;">
                    <div class="col-md-3">
                        <ul class="list-group">
                            <li class="list-group-item active"><h5>Credit</h5></li>
                            <li class="list-group-item" style="font-size: 20px;">{{Auth::user()->getCredit()}}€</li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="list-group">
                            <li class="list-group-item active"><h5>Current bet amount</h5></li>
                            <li class="list-group-item" style="font-size: 20px;">{{$currentBetAmount}}€</li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="list-group">
                            <li class="list-group-item active"><h5>Current possible win</h5></li>
                            <li class="list-group-item" style="font-size: 20px;">{{$currentBetPossibleWin}}€</li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="list-group">
                            <li class="list-group-item active"><h5>Current possible clear win</h5></li>
                            <li class="list-group-item" style="font-size: 20px;">{{$currentBetPossibleClearWin}}€</li>
                        </ul>
                    </div>
                </div>
            </div>

            <br class="clear">

            <div class="basic-content basic-content-filters basic-content-left">
                <h4>Filters</h4>
                <ul class="basic-filters">
                    <li class="@if ($name == "today") active @endif"><a href="/home?from={{\Carbon\Carbon::now()->setTime(0, 0)->getTimestamp()}}&name=today"><span class="fas fa-filter"></span> Today</a></li>
                    <li class="@if ($name == "last-7-days") active @endif"><a href="/home?from={{\Carbon\Carbon::now()->subDays(7)->setTime(0, 0)->getTimestamp()}}&name=last-7-days"><span class="fas fa-filter"></span> Last 7 days</a></li>
                    <li class="@if ($name == "last-30-days") active @endif"><a href="/home?from={{\Carbon\Carbon::now()->subDays(30)->setTime(0, 0)->getTimestamp()}}&name=last-30-days"><span class="fas fa-filter"></span> Last 30 days</a></li>
                    <li class="@if ($name == "this-week") active @endif"><a href="/home?from={{\Carbon\Carbon::now()->startOfWeek()->setTime(0, 0)->getTimestamp()}}&name=this-week"><span class="fas fa-filter"></span> This week</a></li>
                    <li class="@if ($name == "this-month") active @endif"><a href="/home?from={{\Carbon\Carbon::now()->startOfMonth()->setTime(0, 0)->getTimestamp()}}&name=this-month"><span class="fas fa-filter"></span> This month</a></li>
                    <li class="@if ($name == "this-quartal") active @endif"><a href="/home?from={{\Carbon\Carbon::now()->startOfQuarter()->setTime(0, 0)->getTimestamp()}}&name=this-quartal"><span class="fas fa-filter"></span> This quartal</a></li>
                    <li class="@if ($name == "this-year") active @endif"><a href="/home?from={{\Carbon\Carbon::now()->startOfYear()->setTime(0, 0)->getTimestamp()}}&name=this-year"><span class="fas fa-filter"></span> This year</a></li>
                    <br class="clear">
                </ul>
            </div>


            <div class="basic-content basic-content-right">
                <h4>Tickets performance:</h4>
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
            </div>

            <div class="basic-content basic-content-left" style="margin-top: 40px;">
                <h4 style="padding-top: 15px;">Portfolio performance</h4>
                <p>Explore how your profit behaves over time. </p>
                <canvas id="myChartA"></canvas>
            </div>

            <div class="basic-content basic-content-right" style="margin-top: 40px;">
                <h4>Rounds performance:</h4>
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
                <a href="/dashboard/marcingale/detailed" class="btn btn-primary">Marcingale detailed</a>
            </div>

            <br class="clear">

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
                label: 'Portfolio performance',
                borderColor: "red",
                data: @php echo json_encode(array_values($betTicketsChartData)) @endphp,
                fillColor: "rgba(151,249,190,0.5)",
                strokeColor: "rgba(255,255,255,1)",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                fill: true
            },]
        },
        options: {
            responsive: true,
            tooltips: {
                mode: 'index',
                intersect: false
            },
            hover: {
                mode: 'index',
                intersect: false
            },
            elements: {
                point: {
                    radius: 0,
                    hitRadius: 20,
                    hoverRadius: 5,
                }
            }
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
                backgroundColor: "grey",
                borderWidth: "1"
            },]
        },
        options: {
            responsive: true,
            hover: {
                mode: 'nearest',
                intersect: true
            },
            elements: { 
                point: { 
                    radius: 0,
                    hitRadius: 10, 
                    hoverRadius: 5,
                } 
            },
            steppedLine: true
        }
    };

    window.onload = function() {
        var ctxa = document.getElementById('myChartA').getContext('2d');
        window.myLine = new Chart(ctxa, configa);

        // var ctxb = document.getElementById('myChartB').getContext('2d');
        // window.myLine = new Chart(ctxb, configb);
    };

</script>
@endsection
