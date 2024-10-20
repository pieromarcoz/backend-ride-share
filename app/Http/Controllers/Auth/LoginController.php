<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function submit(Request $request)
    {
        // validate the phone number
        $request->validate([
            'phone' => 'required|numeric|min:9',
            'name' => 'required',
        ]);

        // find or create user model
        $user = User::firstOrCreate([
            'name' => $request->name,
            'phone' => '+'.$request->phone,
        ]);

        if (!$user) {
            return response()->json([
                'message' => 'No se ha podido crear el usuario',
            ], 401);
        }

        // send the user one-time use code
        $user->notify(new LoginNeedsVerification());

        // return back a response
        return response()->json([
            'message' => 'Text message notification sent!',
        ]);
    }

    public function verify(Request $request)
    {
        // validate the incoming request
        $request->validate([
            'phone' => 'required|numeric|min:10',
            'login_code' => 'required|numeric|between:100000,999999',
        ]);

        // find the user
        $user = User::where('phone', $request->phone)
            ->where('login_code', $request->login_code)
            ->first();

        // is the code provided the some one saved?

        // if so, return back an auth token
        if ($user) {
            $user->update(['login_code' => null]);
            return $user->createToken($request->login_code)->plainTextToken;
        }

        // if not, return back a message
        return response()->json([
            'message' => 'Invalid login code',
        ], 401);
    }
}
