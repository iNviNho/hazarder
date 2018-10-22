@extends('layouts.app')

@section('content')
    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="basic-content">
                    <h4>Settings</h4>
                    <p>Set your desired settings for each of the supported betting providers.</p>
                    <hr>
                    <form method="POST" action="/settings" enctype="multipart/form-data">
                        @csrf

                        @foreach ($settings->get() as $setting)
                            <h5>
                                <span class="fas fa-user-tie"></span> Betting provider: <strong>{{$setting->bettingProvider()->first()->name}}</strong>
                                @if ($setting->bettingProvider()->first()->active)
                                    <span class="fas fa-lightbulb" style="color: lawngreen; float: right;"
                                          data-placement="bottom" data-toggle="tooltip"
                                          title="This betting provider is enabled"></span>
                                @else
                                    <span class="fas fa-lightbulb" style="color: red; float: right;"
                                          data-placement="bottom" data-toggle="tooltip"
                                          title="This betting provider is temporarily disabled"></span>
                                @endif
                            </h5>
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">Username</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="username-{{$setting->id}}" value="{{$setting->username}}" required autofocus>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">Password</label>
                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="password-{{$setting->id}}" value="{{$setting->password}}" required autofocus>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">Max marcingale open bets</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="max_marcingale-{{$setting->id}}" value="{{$setting->max_marcingale}}" required autofocus>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">Max marcingale level fail</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="max_marcingale_level-{{$setting->id}}" value="{{$setting->max_marcingale_level}}" required autofocus>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">Bet amount</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="bet_amount-{{$setting->id}}" value="{{$setting->bet_amount}}" required autofocus>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">Marcingale finish</label>
                                <div class="col-md-6">
                                    <input type="hidden" name="marcingale_finish-{{$setting->id}}" value="{{$setting->marcingale_finish}}">
                                    <input type="checkbox" class="checkbox-switch" style="width: 25px; height: 25px;"  @if ($setting->marcingale_finish == 1) checked @endif>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">Active</label>
                                <div class="col-md-6">
                                    <input type="hidden" name="active-{{$setting->id}}" value="{{$setting->active}}">
                                    <input type="checkbox" class="checkbox-switch-active" style="width: 25px; height: 25px;"  @if ($setting->active == 1) checked @endif>
                                </div>
                            </div>
                        @endforeach
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
                            <span class="fas fa-info-circle"></span> <strong>Bet amount</strong> - Starting bet amount for a first ticket of marcingale round<br>
                            <span class="fas fa-info-circle"></span> <strong>Marcingale finish</strong> - By having this checkbox checked, no new marcingale rounds will be started<br>
                            <span class="fas fa-info-circle"></span> <strong>Active</strong> - By setting this value, you can enable/disable this betting provider.
                        </p>
                </div>

            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function() {

            $(".checkbox-switch, .checkbox-switch-active").change(function() {
                if(this.checked) {
                    $(this).prev().attr("value", 1);
                } else {
                    $(this).prev().attr("value", 0);
                }
            });

        });
    </script>
@endsection
