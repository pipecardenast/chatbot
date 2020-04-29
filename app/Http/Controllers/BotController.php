<?php

namespace App\Http\Controllers;

use App\User;
use App\Account;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use danielme85\CConverter\Currency;

class BotController extends Controller
{
    function currencyExchange(Request $request) {
        try {
            $this->validate($request, [
                'from' => 'required|size:3',
                'to' => 'required|size:3',
                'amount' => 'required|numeric|gt:0'
            ]);
            $data = $request->json()->all();
            // $result = Currency::conv($data['from'], $data['to'], $data['amount'], $decimals = 2);
            $result = $this->exchangeMoney($data['from'], $data['to'], $data['amount']);
            
            return response()->json("{'success':true, 'data': '" . $data['amount'] . " " . $data['from'] . " = $result " . $data['to'] . "'}", 200);
        } catch (ValidationException $ve) {
            return response()->json("{'success':false, 'error':" . json_encode($ve->errors()) . "}", 422);
        } catch (Exception $e) {
            return response()->json("{'success':false, 'error':'" . $e->response . "'}", 401);
        }
    }

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

    function signup(Request $request) {
        try {
            $this->validate($request, [
                'name' => 'required|min:4',
                'email' => 'email:rfc,dns|required|unique:users,email',
                'password' => 'required|min:6',
                'currency' => 'required|size:3'
            ]);
            // If the validations pass the process continues
            $data = $request->json()->all();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'api_token' => Str::random(60)
            ]);
            $account = new Account([
                'currency' => $data['currency'],
                'balance' => 0
            ]);
            $user->accounts()->save($account);

            return response()->json("{'success':true, 'data': " . $user->toJson() . "}", 201);
        } catch (ValidationException $ve) {
            return response()->json("{'success':false, 'error':" . json_encode($ve->errors()) . "}", 422);
        } catch (Exception $e) {
            return response()->json("{'success':false, 'error':'" . $e->response . "'}", 401);
        }
    }

    function login(Request $request) {
        try {
            $this->validate($request, [
                'email' => 'email:rfc,dns|required|exists:users,email',
                'password' => 'required'
            ]);
            // If the validations pass the process continues
            $data = $request->json()->all();
            $user = User::where('email', $data['email'])->first();

            if ($user && Hash::check($data['password'], $user->password)) {
                return response()->json("{'success':true, 'data': " . $user->toJson() . "}", 200);
            } else {
                return response()->json("{'success':false, 'error':'email or password incorrect'}", 406);
            }
        } catch (ValidationException $ve) {
            return response()->json("{'success':false, 'error':" . json_encode($ve->errors()) . "}", 422);
        } catch (ModelNotFoundException $e) {
            return response()->json("{'success':false, 'error':'email / password incorrect'}", 406);
        }

        return response()->json("{'success':false, 'error':'you are not authorized'}", 401);
    }

    function setCurrencyAccount(Request $request) {
        try {
            $this->validate($request, [
                'currency' => 'required|size:3',
            ]);
            // If the validations pass the process continues
            $data = $request->json()->all();
            $account = User::firstWhere('api_token', $request->header('api-token'))->accounts;
            $account->currency = $data['currency'];
            $account->save();

            return response()->json("{'success':true, 'data': " . $account->toJson() . "}", 200);
        } catch (ValidationException $ve) {
            return response()->json("{'success':false, 'error':" . json_encode($ve->errors()) . "}", 422);
        } catch (Exception $e) {
            return response()->json("{'success':false, 'error':'" . $e->response . "'}", 401);
        }
    }

    function deposit(Request $request) {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric|gt:0',
                'currency' => 'required|size:3'
            ]);
            // If the validations pass the process continues
            $data = $request->json()->all();
            // Get the user associated to the api-token provided
            $account = User::firstWhere('api_token', $request->header('api-token'))->accounts;
            // Create the transation with the information provided
            $data['amount'] = number_format(floatval($data['amount']), 2, '.', '');
            $transaction = new Transaction([
                'type' => 'deposit',
                'amount' => $data['amount'],
                'currency' => $data['currency']
            ]);
            // Add the new transaction record
            $account->transactions()->save($transaction);
            // Update the account with the new balance
            $account->balance = number_format(round($account->balance, 2) + $data['amount'], 2, '.', '');
            $account->save();

            return response()->json("{'success':true, 'data': {'account': " . $account->toJson() . ", 'transaction': " . $transaction->toJson() . "}}", 200);
        } catch (ValidationException $ve) {
            return response()->json("{'success':false, 'error':" . json_encode($ve->errors()) . "}", 422);
        } catch (Exception $e) {
            return response()->json("{'success':false, 'error':'" . $e->response . "'}", 401);
        }
    }

    function withdraw(Request $request) {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric|gt:0',
                'currency' => 'required|size:3'
            ]);
            // If the validations pass the process continues
            $data = $request->json()->all();
            // Get the user associated to the api-token provided
            $account = User::firstWhere('api_token', $request->header('api-token'))->accounts;
            $data['amount'] = number_format(floatval($data['amount']), 2, '.', '');

            if (round($account->balance, 2) < round($data['amount'], 2)) {
                return response()->json("{'success':false, 'error':'Insufficient funds'}", 422);
            }

            // Create the transation with the information provided
            $transaction = new Transaction([
                'type' => 'withdraw',
                'amount' => $data['amount'],
                'currency' => $data['currency']
            ]);
            // Add the new transaction record
            $account->transactions()->save($transaction);
            // Update the account with the new balance
            $account->balance = number_format(round($account->balance, 2) - $data['amount'], 2, '.', '');
            $account->save();

            return response()->json("{'success':true, 'data': {'account': " . $account->toJson() . ", 'transaction': " . $transaction->toJson() . "}}", 200);
        } catch (ValidationException $ve) {
            return response()->json("{'success':false, 'error':" . json_encode($ve->errors()) . "}", 422);
        } catch (Exception $e) {
            return response()->json("{'success':false, 'error':'" . $e->response . "'}", 401);
        }
    }

    function balance(Request $request) {
        try {
            $this->validate($request, [
                'currency' => 'size:3'
            ]);
            // If the validations pass the process continues
            $data = $request->json()->all();
            // Get the user associated to the api-token provided
            $account = User::firstWhere('api_token', $request->header('api-token'))->accounts;
            // Create the transation with the information provided
            $transaction = new Transaction([
                'type' => 'balance',
                'currency' => array_key_exists('currency', $data) ? $data['currency'] : null
            ]);
            // Add the new transaction record
            $account->transactions()->save($transaction);
            // Update the account with the new balance
            $account->balance = number_format($account->balance, 2, '.', '');

            return response()->json("{'success':true, 'data': {'account': " . $account->toJson() . ", 'transaction': " . $transaction->toJson() . "}}", 200);
        } catch (ValidationException $ve) {
            return response()->json("{'success':false, 'error':" . json_encode($ve->errors()) . "}", 422);
        } catch (Exception $e) {
            return response()->json("{'success':false, 'error':'" . $e->response . "'}", 401);
        }
    }
}
