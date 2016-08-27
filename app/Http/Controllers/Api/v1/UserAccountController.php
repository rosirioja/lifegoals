<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface;
use App\Contracts\UserAccountInterface;

use DB, Log, Exception, Validator;

class UserAccountController extends BaseController
{
    public function __construct(
        UserInterface $userInterface,
        UserAccountInterface $userAccountInterface)
    {
        $this->user = $userInterface;
        $this->userAccount = $userAccountInterface;

        $this->account_type = ['UNIONBANK', 'COINSPH'];

        DB::enableQueryLog();
    }

    /**
     * Add new account per user
     *
     * @param Request $request
     * @return json
     */
    public function getAccounts(Request $request)
    {
        try {
            $isBadRequest = false; // switching of http response code

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|int',
                'type' => 'required',
                'access_token' => 'required'
            ]);

            if ($validator->fails()) {
                $isBadRequest = true;
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            $user_id = $request->input('user_id');
            $type = $request->input('type');
            $access_token = $request->input('access_token');

            // VALIDATION -STARTS
            if (! $this->user->exists(['id' => $user_id, 'active' => 1])) {
                throw new Exception("Error Processing Request: Invalid User");
            }

            if (! in_array($type, $this->account_type)) {
                throw new Exception("Error Processing Request: Invalid Account Type");
            }
            // VALIDATION - ENDS

            // Connect with Account Service to get info
            // $accountService = new AccountService($type, $access_token);
            // $response = $accountService->getAccountInfo();


            $params = [
                'user_id' => $user_id,
                'type' => $type,
                'access_token' => $access_token,
                'account_name' => '',
                'account_no' => '',
                'target_address' => '',
                'current_balance' => 0
            ];

            if (! $data = $this->userAccount->store($params)) {
                throw new Exception("Error Processing Request: Cannot Add Account");
            }

        } catch (Exception $e) {
            $code = $isBadRequest ? 400 : 500;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $code);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }
}
