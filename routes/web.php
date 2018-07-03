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

//Route::get('/', function () {
//    return \Illuminate\Support\Facades\Artisan::call("tickets:prepare");
//});


Route::get('/', "HomeController@checkLogin");

Route::get('/tickets', "TicketController@showTickets");

Route::get('/tickets/approve/{ticketID}', "TicketController@approve");
Route::get('/tickets/disapprove/{ticketID}', "TicketController@disapprove");
Route::get('/tickets/bet/{ticketID}', "TicketController@bet");
Route::get('/tickets/checkresult/{ticketID}', "TicketController@checkresult");
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


// continuos integration
Route::post("/git/pull/please", function() {

    // go to root folder and pull
    // for this command we must have upstream to certain branch
    // should work like this for noww
    shell_exec("cd /var/www/hazarder && git pull");

    shell_exec("cd /var/www/hazarder && sudo git pull");

    return response([
        "result" => "ok"
    ], 200);
});
