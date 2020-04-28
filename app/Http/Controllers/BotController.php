<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class BotController extends Controller
{
    //
    function index(Request $request) {
      if ($request->isJson()) {
        return response()->json("Hola mundo", 200);
      }

      return "";
    }

    function curencyExchange(Request $request) {
      if ($request->isJson()) {
        return response()->json("It is a curencyExchange", 200);
      }

      return response()->json("{'error':'you are not authorized'}", 401);
    }

    function signUp(Request $request) {
      if ($request->isJson()) {
        $data = $request->json()->all();
        $user = User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'password' => Hash::make($data['password']),
          'api_token' => Str::random(60)
        ]);

        return response()->json($user, 201);
      }

      return response()->json("{'error':'you are not authorized'}", 401);
    }

    function login(Request $request) {
      if ($request->isJson()) {
        try {
          $data = $request->json()->all();
          $user = User::where('email', $data['email'])->first();

          if ($user && Hash::check($data['password'], $user->password)) {
            return response()->json($user, 200);
          } else {
            return response()->json("{'error':'email / password incorrect'}", 406);
          }
        } catch (ModelNotFoundException $e) {
          return response()->json("{'error':'email / password incorrect'}", 406);
        }
      }

      return response()->json("{'error':'you are not authorized'}", 401);
    }

    function deposit(Request $request) {
      if ($request->isJson()) {
        return response()->json("It is a deposit", 200);
      }

      return response()->json("{'error':'you are not authorized'}", 401);
    }

    function withdraw(Request $request) {
      if ($request->isJson()) {
        return response()->json("It is a withdraw", 200);
      }

      return response()->json("{'error':'you are not authorized'}", 401);
    }

    function balance(Request $request) {
      if ($request->isJson()) {
        return response()->json("It is a balance", 200);
      }

      return response()->json("{'error':'you are not authorized'}", 401);
    }
}
