<?php

namespace App\Http\Controllers;

use App\User;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TransactionsController extends Controller
{
    function deposit(Request $request) {
        try {
            // Checking if the deposit information provided is valid
            $data = $this->validate($request, [
                'amount' => 'required|numeric|gt:0',
                'currency' => 'required|size:3'
            ]);
            // Get the user current user information
            $user = Auth::user();
            // Get the account information associated to the current user
            $account = $user->accounts;
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

            return response()->json(['success' => true, 'data' => ['account' => $account, 'transaction' => $transaction]], 200);
        } catch (ValidationException $ve) {
            return response()->json(['success' => false, 'error' => $ve->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->response], 401);
        }
    }

    function withdraw(Request $request) {
        try {
            $data = $this->validate($request, [
                'amount' => 'required|numeric|gt:0',
                'currency' => 'required|size:3'
            ]);
            // Get the user current user information
            $user = Auth::user();
            // Get the account information associated to the current user
            $account = $user->accounts;
            $data['amount'] = number_format(floatval($data['amount']), 2, '.', '');

            if (round($account->balance, 2) < round($data['amount'], 2)) {
                return response()->json(['success' => false, 'error' => 'Insufficient funds'], 422);
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

            return response()->json(['success' => true, 'data' =>  ['account' => $account, 'transaction' => $transaction]], 200);
        } catch (ValidationException $ve) {
            return response()->json(['success' => false, 'error' => $ve->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->response], 401);
        }
    }

    function getBalance(Request $request) {
        try {
            $data = $this->validate($request, [
                'currency' => 'size:3'
            ]);
            // Get the user current user information
            $user = Auth::user();
            // Get the account information associated to the current user
            $account = $user->accounts;
            // Create the transation with the information provided
            $transaction = new Transaction([
                'type' => 'balance',
                'currency' => array_key_exists('currency', $data) ? $data['currency'] : null
            ]);
            // Add the new transaction record
            $account->transactions()->save($transaction);
            // Update the account with the new balance
            $account->balance = number_format($account->balance, 2, '.', '');

            return response()->json(['success' => true, 'data' => ['account' => $account, 'transaction' => $transaction]], 200);
        } catch (ValidationException $ve) {
            return response()->json(['success' => false, 'error' => $ve->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->response], 401);
        }
    }
}
