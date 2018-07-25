@extends('layouts.app')

@section('content')
    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="basic-content">
                    <h1>Settings</h1>

                    <form method="POST" action="/settings">
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
                                <input type="text" class="form-control" name="password" value="{{$settings->password}}" required autofocus>
                            </div>
                        </div><div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Max one ten open bets</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="max_oneten" value="{{$settings->max_oneten}}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Max one twenty open bets</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="max_onetwenty" value="{{$settings->max_onetwenty}}" required autofocus>
                            </div>
                        </div><div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Max one marcingale open bets</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="max_marcingale" value="{{$settings->max_marcingale}}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Max one opposite open bets</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="max_opposite" value="{{$settings->max_opposite}}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Max bet amount</label>
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
                </div>

            </div>
        </div>
    </div>
@endsection
