<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function showSettings(Request $request) {

        $settings = Auth::user()->getSettings()->first();
        return view("user.settings", [
            "settings" => $settings
        ]);
    }

    public function updateSettings(Request $request) {

        $settings = Auth::user()->getSettings()->first();

        $settings->update($request->all());

        $request->session()->flash('msg', 'Settings successfully update!');
        return view("user.settings", [
            "settings" => $settings
        ]);
    }

}
