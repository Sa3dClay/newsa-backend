<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('API TOKEN')->plainTextToken;

            return response(['token' => $token]);
        } catch (\Exception $e) {
            return response(['error' => $e], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::firstWhere('email', $request->email);

            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('API TOKEN')->plainTextToken;
            } else {
                return response([
                    'errors' => ['password' => 'Invalid password']
                ], 400);
            }

            return response(['token' => $token]);
        } catch (\Exception $e) {
            return response(['error' => $e], 500);
        }
    }

    public function user()
    {
        return response([
            'user' => auth()->user()
        ]);
    }
}
