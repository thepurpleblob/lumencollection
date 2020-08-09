<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\User;
use Carbon\Carbon;

class AdminController extends Controller {

    public function login(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (!$user = User::where([
            'email' => $email,
            'password' => $hash
        ])) {
            return response('Unauthorized.', 401);
        } else {
            $api_token = sha1($email . time());
            $user->api_token = $api_token;
            $user->api_token_updated = Carbon::now();
            $user->save();

            return response()->json($user);
        }
    }

}