<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface;
use App\Contracts\UserAccountInterface;
use App\Contracts\TransactionInterface;

use DB, Log, Exception, Validator;

class UserAccountController extends BaseController
{
    public function __construct(
        UserInterface $userInterface,
        UserAccountInterface $userAccountInterface,
        TransactionInterface $transactionInterface)
    {
        $this->user = $userInterface;
        $this->userAccount = $userAccountInterface;
        $this->transaction = $transactionInterface;

        $this->account_type = ['UNIONBANK', 'COINSPH'];

        DB::enableQueryLog();
    }

    /**
     * Get All User Accounts
     * get user accounts per user
     *
     * @param Request $request
     * @return json
     */
    public function getAccounts(Request $request)
    {
        try {
            $isBadRequest = false; // switching of http response code

            $user_id = $request->input('user_id');

            if (empty($user_id)) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: No parameter specified.");
            }

            $params = [
                'where' => [
                    'and' => [
                        ['field' => 'user_id', 'value' => $user_id]
                    ]
                ]
            ];

            if (! $data = $this->userAccount->getList($params)) {
                throw new Exception("Error Processing Request: Cannot Retrieve User Account");
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

    /**
     * Add new account per user
     *
     * @param Request $request
     * @return json
     */
    public function postAccounts(Request $request)
    {
        try {
            $isBadRequest = false; // switching of http response code

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
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
                $isBadRequest = true;
                throw new Exception("Error Processing Request: Invalid User");
            }

            if (! in_array($type, $this->account_type)) {
                $isBadRequest = true;
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

    /**
     * Get User Accounts per User
     * Get Transaction History per user
     *
     * @param int user id
     * @return json
     */
    public function getPortfolio($user_id = '')
    {
        try {
            $isBadRequest = false; // switching of http response code

            // VALIDATION - STARTS
            if (empty($user_id)) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: No Parameters Specified.");
            }

            if (! $this->user->exists(['id' => $user_id, 'active' => 1])) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: Invalid User");
            }
            // VALIDATION - ENDS

            $params = [
                'where' => [
                    'and' => [
                        ['field' => 'user_id', 'value' => $user_id]
                    ]
                ]
            ];

            if (! $accounts = $this->userAccount->getList($params)) {
                throw new Exception("Error Processing Request: Cannot Retrieve Account Data");
            }

            if (! $transactions = $this->transaction->getList($params)) {
                throw new Exception("Error Processing Request: Cannot Retrieve Transaction Data");
            }

            $data = [
                'accounts' => $accounts,
                'transactions' => $transactions
            ];

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
