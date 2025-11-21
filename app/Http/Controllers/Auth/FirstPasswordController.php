<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FirstPasswordController extends Controller
{
    public function show()
    {
        return view('auth.first_password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required','string','min:8','confirmed'],
        ]);
        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('status','Wachtwoord gewijzigd.');
    }
}
