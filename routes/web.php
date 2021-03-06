<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Starting point for every blank request
 */
Route::get('/', "HomeController@checkLogin");


/**
 * Authorized access
 */
Route::group(["middleware" => ["auth", "authorized"]], function() {

    Route::get('/home/{name?}', 'HomeController@index')->name('home');

    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('/dashboard/marcingale/detailed/{name?}', 'DashboardController@marcingale')->name('marcingaledetailed');

    Route::get('/my-tickets', "TicketController@showMyTickets");
    Route::get('/tickets', "TicketController@showTickets");

    Route::get('/tickets/approve/{ticketID}', "TicketController@approve");
    Route::get('/tickets/disapprove/{ticketID}', "TicketController@disapprove");
    Route::get('/tickets/bet/{ticketID}', "TicketController@bet");
    Route::get('/tickets/checkresult/{ticketID}', "TicketController@checkresult");

    Route::get("/match/{matchID}", "MatchesController@showMatch");
    Route::post("/match/marcingale/continue", "MatchesController@continueMarcingaleRound");
    Route::get("/matches", "MatchesController@showMatches");

    Route::get('/user/update-credit', 'UserController@updateCredit');

    Route::get("/my-logs", "LogsController@showMyLogs");

    Route::get("/settings", "UserController@showSettings");
    Route::post("/settings", "UserController@updateSettings");
});

/**
 * Register LOGIN & REGISTER routes
 */
Auth::routes();