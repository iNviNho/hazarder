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
        $user = Auth::user();

        $values = $request->all();

        // lets validate bet_amount
        if (bccomp($values["bet_amount"], "0.5", 2) < 0) {
            $values["bet_amount"] = "0.5";
        }

        // lets check if photo was uploaded
        $file = $request->file('bg_image');
        if ($request->hasFile("bg_image") && $file->isValid()) {
            // lets set it
            $values["bg_image"] = "images/user-images/" . $user->id . ".". $file->getClientOriginalExtension();
            $file->move("images/user-images", $user->id . ".". $file->getClientOriginalExtension());
        } else {
            $values["bg_image"] = $settings->bg_image;
        }

        if (array_key_exists("marcingale_finish", $values) && $values["marcingale_finish"] == "on") {
            $values["marcingale_finish"] = 1;
        } else {
            $values["marcingale_finish"] = 0;
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
