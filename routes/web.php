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

Route::get('/', function () {
    return \Illuminate\Support\Facades\Artisan::call("tickets:prepare");
});

Route::get('/tickets', "TicketController@showTickets");

Route::get('/tickets/approve/{ticketID}', "TicketController@approve");
Route::get('/tickets/disapprove/{ticketID}', "TicketController@disapprove");
Route::get('/tickets/bet/{ticketID}', "TicketController@bet");
Route::get('/tickets/checkresult/{ticketID}', "TicketController@checkresult");