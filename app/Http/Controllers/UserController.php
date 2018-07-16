<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class UserController extends Controller
{

    public function showSettings(Request $request) {
        return view("user.settings");
    }

    public function updateSettings(Request $request) {

    }

}
