<?php

namespace App\Http\Controllers;

use App\User;
use App\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AccountsController extends Controller
{
    function setCurrency(Request $request) {
        try {
            // Checking if the currency provided is valid
            $data = $this->validate($request, [
                'currency' => 'required|size:3',
            ]);
            // Get the user current user information
            $user = Auth::user();
            // Get the account information associated to the current user
            $account = $user->accounts;
            // Changing the currency 
            $account->currency = $data['currency'];
            // Saving the changes
            $account->save();

            return response()->json(['success' => true, 'data' => $account->toJson()], 200);
        } catch (ValidationException $ve) {
            // Return an error message if at least one field didn't pass the validations
            return response()->json(['success' => false, 'error' => 'No valid information provided'], 422);
        } catch (Exception $e) {
            // Return the response as un-success and a message that describes the error
            return response()->json(['success' => false, 'error' => 'Something went wrong, please try again or contact the support team'], 401);
        }
    }
}
