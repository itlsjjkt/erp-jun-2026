<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User; 

class ErpUserApiController extends Controller
{

    public function lookup(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($request->query('email'));

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['found' => false], 200);
        }

        return response()->json([
            'found' => true,
            'user'  => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'password_hash' => $user->password, 
            ],
        ], 200);
    }
}
