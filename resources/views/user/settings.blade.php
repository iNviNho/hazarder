@extends('layouts.app')

@section('content')
    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="basic-content">
                    <h4>Settings</h4>
                    <hr>
                    <form method="POST" action="/settings" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Username</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="username" value="{{$settings->username}}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Password</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password" value="{{$settings->password}}" required autofocus>
                            </div>
                        </div>
                        {{--<div class="form-group row">--}}
                            {{--<label for="email" class="col-sm-4 col-form-label text-md-right">Max one ten open bets</label>--}}
                            {{--<div class="col-md-6">--}}
                                {{--<input type="text" class="form-control" name="max_oneten" value="{{$settings->max_oneten}}" required autofocus>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="form-group row">--}}
                            {{--<label for="email" class="col-sm-4 col-form-label text-md-right">Max one twenty open bets</label>--}}
                            {{--<div class="col-md-6">--}}
                                {{--<input type="text" class="form-control" name="max_onetwenty" value="{{$settings->max_onetwenty}}" required autofocus>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Max marcingale open bets</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="max_marcingale" value="{{$settings->max_marcingale}}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Max marcingale level fail</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="max_marcingale_level" value="{{$settings->max_marcingale_level}}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Marcingale finish</label>
                            <div class="col-md-6">
                                <input type="checkbox" style="width: 25px; height: 25px;" name="marcingale_finish" @if ($settings->marcingale_finish == 1) checked @endif>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Bet amount</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="bet_amount" value="{{$settings->bet_amount}}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right"></label>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success">UPDATE</button>
                            </div>
                        </div>
                    </form>
                    <hr>
                        <p>
                            <span class="fas fa-info-circle"></span> <strong>Username</strong> - Your username or nickname on betting website<br>
                            <span class="fas fa-info-circle"></span> <strong>Password</strong> - Your password on betting website<br>
                            <span class="fas fa-info-circle"></span> <strong>Max marcingale open bets</strong> - By this setting you specify maximum amount of marcingale
                            tickets that can be open and not decided<br>
                            <span class="fas fa-info-circle"></span> <strong>Max marcingale level fail</strong> - By this setting you specify maximum amount of marcingale
                            tickets in one marcingale round. For example by specifying number 4, it means that
                            maximum 4 tickets could be automatically bet to the particular round<br>
                            <span class="fas fa-info-circle"></span> <strong>Marcingale finish</strong> - By having this checkbox checked, no new marcingale rounds will be started<br>
                            <span class="fas fa-info-circle"></span> <strong>Bet amount</strong> - Starting bet amount for a first ticket of marcingale round
                        </p>
                </div>

            </div>
        </div>
    </div>
@endsection
