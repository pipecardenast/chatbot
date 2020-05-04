<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    function login(Request $request) {
        try {
            // Checking if the login information provided is valid
            $this->validate($request, [
                'email' => 'email:rfc,dns|required|exists:users,email',
                'password' => 'required'
            ]);
            $data = $request->only('email', 'password');
            // Get the user information for the email sent if it exists
            $user = User::where('email', $data['email'])->first();
            // Checking that credentials provided exist
            if ($user && Hash::check($data['password'], $user->password)) {
                $token = JWTAuth::attempt($data);
                return response()->json(['success' => true, 'data' =>  ['user' => $user, 'token' => $token]], 200);
            } else {
                return response()->json(['success' => false, 'error' => 'The email or password is incorrect'], 401);
            }
        } catch (ValidationException $ve) {
            // Return an error message if at least one field didn't pass the validations
            return response()->json(['success' => false, 'error' => 'No valid information provided'], 422);
        } catch (Exception $e) {
            // Return the response as un-success and a message that describes the error
            return response()->json(['success' => false, 'error' => 'Something went wrong, please try again or contact the support team'], 401);
        }
    }

    function refreshToken() {
        try {
            $token = JWTAuth::getToken();
            $token = JWTAuth::refresh($token);
            return response()->json(['success' => true, 'data' => ['token' => $token]], 201);
        } catch (TokenExpiredException $exe) {
            // Return an error message if the token was expired
            return response()->json(['success' => false, 'error' => 'You need to login again'], 422);
        } catch (TokenBlacklistedException $ble) {
            // Return an error message if the token was blacklisted
            return response()->json(['success' => false, 'error' => 'You need to login again'], 422);
        }
    }

    function logout() {
        try {
            $token = JWTAuth::getToken();
            $token = JWTAuth::invalidate($token);
            return response()->json(['success' => true, 'data' => 'You were logged out correctly'], 200);
        } catch (JWTException $ex) {
            // Return an error message if something went wrong
            return response()->json(['success' => false, 'error' => 'The logout failed. Please try again'], 422);
        }
    }
}
