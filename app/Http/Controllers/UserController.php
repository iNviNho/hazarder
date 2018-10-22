<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function showSettings() {

        $settings = Auth::user()->getSettings();
        return view("user.settings", [
            "settings" => $settings
        ]);
    }

    public function updateSettings(Request $request) {

        $data = $request->except("_token");

        foreach ($data as $key => $value) {

            $del = explode("-", $key);
            $column = $del[0];
            $id = $del[1];

            $setting = Settings::find($id);
            $setting->$column = $value;
            $setting->save();
        }

        $request->session()->flash('msg', 'Settings successfully update!');
        return redirect("/settings");
    }

    public function updateCredit(Request $request) {

        $user = Auth::user();
        $user->updateCredit();

        $request->session()->flash('msg', 'Your credit was updated!');

        return redirect("/home");
    }

}
