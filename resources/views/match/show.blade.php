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
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection
