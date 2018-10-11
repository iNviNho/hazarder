@extends('layouts.app')

@section('content')
    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="basic-content">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="row">
                                <div class="offset-md-1 col-md-10">
                                    <h4>Match: </h4>
                                    <p>All available information regarding match</p>
                                    <table class="table table-bordered table-striped table-hovered">
                                        <thead>
                                        <tr>
                                            <td><strong>Key</strong></td>
                                            <td>Value</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($match->getAttributes() as $key => $value)
                                            <tr>
                                                <td><strong>{{$key}}</strong></td>
                                                <td>{{$value}}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td><strong>Bet options</strong></td>
                                            <td>
                                                <table class="table matchbettable">
                                                    <tr>
                                                        @foreach ($match->getMatchBets()->get() as $matchBet)
                                                            <td>{{$matchBet->name}}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        @foreach ($match->getMatchBets()->get() as $matchBet)
                                                            <td><strong>{{$matchBet->value}}</strong></td>
                                                        @endforeach
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="row">

                                <div class="offset-md-1 col-md-10">

                                    <h4>Continue manually failed Marcingale round</h4>
                                    <p>
                                        With this form you have an ability to manually continue any failed Marcingale round.
                                        This is especially good when you want to try to use your knowledge and luck
                                        and try to chnage a failed marcingale round into won one!<br><br>
                                        <span class="fas fa-exclamation-circle"></span> Be aware that the bet amount is totally up to you but general idea would be to have option
                                        <strong>After win your round profit will be</strong> equal at least 0 or any minimal profit
                                        to rollback this loss and let automated bot do the profits for you.
                                    </p>

                                    <hr>

                                    <h5>Bet user ticket</h5>
                                    <form action="/match/marcingale/continue" method="POST">
                                        @csrf
                                        <input type="hidden" name="match-id" value="{{$match->id}}">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Lost marcingale round</label>
                                            <select class="form-control marcingale-round" name="marcingale-round">
                                                @foreach ($marcingaleUserRounds->get() as $marcingaleUserRound)
                                                    <option value="{{$marcingaleUserRound->id}}" data-profit="{{$marcingaleUserRound->getProfit()}}">
                                                        @php echo "ID: " . $marcingaleUserRound->id . " - " . $marcingaleUserRound->level_finished . " levels. Profit: "; echo $marcingaleUserRound->getProfit(); @endphp</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Bet option</label>
                                            <select class="form-control bet-option" name="bet-option">
                                                @foreach ($match->getMatchBets()->get() as $matchBet)
                                                    <option value="{{$matchBet->name}}" data-rate="{{$matchBet->value}}">{{$matchBet->name}} with rate {{$matchBet->value}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Bet amount</label>
                                            <input type="input" name="bet-amount" class="form-control bet-amount" value="1">

                                            <label>Bet win</label>
                                            <input type="input" disabled class="form-control bet-win" >

                                            <label>Bet possible clear win</label>
                                            <input type="input" disabled  class="form-control bet-possible-clear-win" >

                                            <label>After win your round profit will be</label>
                                            <input type="input" disabled  class="form-control round-possible-win-after" >

                                            <label>After loose your round profit will be</label>
                                            <input type="input" disabled  class="form-control round-possible-loose-after" >
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>


    <script>
        $(function() {

            calculateInfo();

            $('.bet-amount').on("input", function() {
                calculateInfo();
            });

            $(".bet-option, .marcingale-round").on('change', function() {
                calculateInfo();
            });

            function calculateInfo() {

                var bet_amount = $(".bet-amount").val();
                var bet_option = $('.bet-option').find(":selected").data("rate");
                var profit = parseFloat( $(".marcingale-round").find(":selected").data("profit") );

                $(".bet-win").val( (bet_amount * bet_option) * 100 / 100);
                $(".bet-possible-clear-win").val( Math.round( (bet_amount * bet_option - bet_amount) * 100) / 100);
                $(".round-possible-win-after").val( Math.round( ( (bet_amount * bet_option - bet_amount) + profit ) * 100) / 100);
                $(".round-possible-loose-after").val( profit - bet_amount);

            }

        })
    </script>
@endsection
