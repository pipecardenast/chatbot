<?php

namespace App\Http\Controllers;

use App\User;
use App\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    protected function exchangeMoney($from, $to, $amount) {
        try {
            $currencies = $from . "_" . $to;
            $requestURL = env('CURRENCY_API_URL') . "/convert?q=$currencies&compact=ultra&apiKey=" . env('CURRENCY_API_KEY');
            $response = Http::get($requestURL);

            if ($response->successful()) {
                $data = $response->json();
                if (array_key_exists($currencies, $data)) {
                    $exchangeRate = floatval($data[$currencies]);
                    $amount = floatval($amount);
                    return number_format(round($amount, 2) * round($exchangeRate, 2), 2, '.', '');
                }
            }

            return $response->throw();
        } catch (RequestException $e) {
            return $response->throw();
        }
    }

    function currencyExchange(Request $request) {
        try {
            // Checking if the money exchage information provided is valid
            $data = $this->validate($request, [
                'from' => 'required|size:3',
                'to' => 'required|size:3',
                'amount' => 'required|numeric|gt:0'
            ]);
            // Get the money exchange for the data sent
            $result = $this->exchangeMoney($data['from'], $data['to'], $data['amount']);
            
            return response()->json(['success' => true, 'data' => $data['amount'] . " " . $data['from'] . " = $result " . $data['to']], 200);
        } catch (ValidationException $ve) {
            // Return the validation errors if at least one field didn't pass the validations
            return response()->json(['success' => false, 'error' =>  $ve->errors()], 422);
        } catch (Exception $e) {
            // Return the response as un-success and a message that describes the error
            return response()->json(['success' => false, 'error' => $e->response], 401);
        }
    }

    function signup(Request $request) {
        try {
            // Checking if the user the information provided is valid
            $data = $this->validate($request, [
                'name' => 'required|min:4',
                'email' => 'email:rfc,dns|required|unique:users,email',
                'password' => 'required|min:6',
                'currency' => 'required|size:3'
            ]);
            // If the validations pass a new user is created with the information provided
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'api_token' => Str::random(60)
            ]);
            // The user was created, so we need to create an account for him without funds
            $account = new Account([
                'currency' => $data['currency'],
                'balance' => 0
            ]);
            $user->accounts()->save($account);

            return response()->json(['success' => true, 'data' => $user->toJson()], 201);
        } catch (ValidationException $ve) {
            // Return the validation errors if at least one field didn't pass the validations
            return response()->json(['success' => false, 'error' => $ve->errors()], 422);
        } catch (Exception $e) {
            // Return the response as un-success and a message that describes the error
            return response()->json(['success' => false, 'error' => $e->response], 401);
        }
    }
}
