<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function showSettings() {

        $settings = Auth::user()->getSettings()->first();
        return view("user.settings", [
            "settings" => $settings
        ]);
    }

    public function updateSettings(Request $request) {

        $settings = Auth::user()->getSettings()->first();

        $values = $request->all();

        // lets validate bet_amount
        if (bccomp($values["bet_amount"], "0.5", 2) < 0) {
            $values["bet_amount"] = "0.5";
        }

        $settings->update($values);

        $request->session()->flash('msg', 'Settings successfully update!');
        return view("user.settings", [
            "settings" => $settings
        ]);
    }

    public function updateCredit(Request $request) {

        $user = Auth::user();
        $user->updateCredit();

        $request->session()->flash('msg', 'Your credit was updated!');

        return redirect("/home");
    }

}
