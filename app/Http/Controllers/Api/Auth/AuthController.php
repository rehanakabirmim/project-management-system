<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'required|string|max:20',
            'password'      => 'required|string|min:6|confirmed',
        ]);

        // Create user
        $user = User::create([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone_number'  => $request->phone_number,
            'password'      => Hash::make($request->password),
        ]);

       

        return response()->json([
            'status'  => 'success',
            'message' => 'User registered successfully',
            'meta'    => [
                'timestamp' => now()->toIso8601String(),
                'version'   => '1.0'
            ]
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required|string',
        ]);

        $user = User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([
                'status'=>'error',
                'message'=>'Invalid credentials',
                'errors'=>null,
                'meta'=>[
                    'timestamp'=>now()->toIso8601String(),
                    'version'=>'1.0'
                ]
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'=>'success',
            'message'=>'Login successful',
            'data'=>[
                'access_token'=>$token,
                'token_type'=>'Bearer'
            ],
            'meta'=>[
                'timestamp'=>now()->toIso8601String(),
                'version'=>'1.0'
            ]
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'=>'success',
            'message'=>'Logged out successfully',
            'data'=>null,
            'meta'=>[
                'timestamp'=>now()->toIso8601String(),
                'version'=>'1.0'
            ]
        ]);
    }

    // Forgot Password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email'=>'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'status'=>'success',
                'message'=>__($status),
                'data'=>null,
                'meta'=>[
                    'timestamp'=>now()->toIso8601String(),
                    'version'=>'1.0'
                ]
            ])
            : response()->json([
                'status'=>'error',
                'message'=>__($status),
                'errors'=>null,
                'meta'=>[
                    'timestamp'=>now()->toIso8601String(),
                    'version'=>'1.0'
                ]
            ],400);
    }


    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'=>'required|email|exists:users,email',
            'token'=>'required|string',
            'password'=>'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function($user,$password){
                $user->forceFill(['password'=>Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json([
                'status'=>'success',
                'message'=>__($status),
                'data'=>null,
                'meta'=>[
                    'timestamp'=>now()->toIso8601String(),
                    'version'=>'1.0'
                ]
            ])
            : response()->json([
                'status'=>'error',
                'message'=>__($status),
                'errors'=>null,
                'meta'=>[
                    'timestamp'=>now()->toIso8601String(),
                    'version'=>'1.0'
                ]
            ],400);
    }

}
