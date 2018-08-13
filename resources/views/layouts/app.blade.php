<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Hazarder | Betting is Real</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="{{ assetn('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

    <!-- Styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="{{ assetn('css/app.css') }}" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ assetn("images/h.png") }}">

</head>
<body class="lenka-bg"
@guest
    style="background-image: url({{ assetn("images/background.jpg") }});"
@else
    @if (is_null(Auth::user()->getSettings()->first()->bg_image))
      style="background-image: url({{ assetn("images/background.jpg") }});"
    @else
        style="background-image: url({{ assetn(Auth::user()->getSettings()->first()->bg_image) }});"
    @endif
@endguest
>
    <div id="app">
        @if(session()->has('msg'))
            <div class="alert alert-info">
                {!! session('msg') !!}
            </div>
        @endif
        <nav class="navbar navbar-expand-md navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <strong>Hazarder</strong>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                    <span class="fa fa-angle-down"  style="color: #FFF; font-size: 28px;"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent" style="text-align: right;">
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @else
                            <div style="color: white; font-weight: bold; font-size: 22px;">
                                {{Auth::user()->credit}}€
                                <a href="/user/update-credit" data-placement="bottom" data-toggle="tooltip"
                                   title="Last updated: {{Auth::user()->getCreditUpdateTime()->format("d.m.Y H:i")}}"
                                    style="padding-left: 10px;">
                                    <span class="fa fa-sync" style="color: #56ddff; font-size: 18px;"></span>
                                </a>
                            </div>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="/dashboard" > <strong>Dashboard</strong></a>
                                    <a class="dropdown-item" href="/matches" > Matches</a>
                                    <a class="dropdown-item" href="/tickets" > Tickets</a>
                                    <a class="dropdown-item" href="/my-tickets" > My Tickets</a>
                                    <a class="dropdown-item" href="/settings" > Settings</a>
                                    <a class="dropdown-item" href="/my-logs" > My Logs</a>

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i>{{ __('Logout') }}</i>
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
