@extends('layouts.app')

@section('content')
    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="basic-content">
                    <h3>Match: </h3>

                    <div>
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
                        <div class="col-md-6">
                            <h3>Continue manually failed Marcingale round</h3>
                            <form action="/match/marcingale/continue" method="POST">
                                @csrf
                                <input type="hidden" name="match-id" value="{{$match->id}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Pick lost marcingale round</label>
                                    <select class="form-control" name="marcingale-round">
                                        @foreach ($marcingaleUserRounds->get() as $marcingaleUserRound)
                                            <option value="@php echo $marcingaleUserRound->id; @endphp">
                                                @php echo "ID: " . $marcingaleUserRound->id . " - " . $marcingaleUserRound->level_finished . " levels. Profit: "; echo $marcingaleUserRound->getProfit(); @endphp</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Bet option</label>
                                    <input type="input" name="bet-option" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Bet amount</label>
                                    <input type="input" name="bet-amount" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection
