<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface;
use App\Contracts\UserAccountInterface;
use App\Contracts\TransactionInterface;

use App\Classes\AccountApi;

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

        $this->account_type = ['UBANK', 'COINS'];

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

            // if UBANK rename to account_no
            $access_token = $request->input('access_token');
            $account_no = '';
            if ($type == 'UBANK') {
                $account_no = $access_token;
            }

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
            $response = $this->connectAccountService([
                'type' => $type,
                'access_token' => $access_token,
                'account_no' => $account_no
            ]);

            if ($response == false) {
                throw new Exception("Error Processing Request: Cannot Retrieve Account Information");
            }

            $params = [
                'user_id' => $user_id,
                'type' => $type,
                'access_token' => $access_token
            ];

            if ($type == 'UBANK') {
                $params['account_name'] = $response['account_name'];
                $params['account_no'] = $response['account_no'];
                $params['current_balance'] = $response['avaiable_balance'];
            }

            if ($type == 'COINS') {
                $params['account_name'] = $response['name'];
                $params['account_no'] = $response['id'];
                $params['target_address'] = $response['default_address'];
                $params['current_balance'] = $response['balance'];
            }

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

            // Connect with Account Service to get info
            $total = 0;
            $updated_accounts = [];
            foreach ($accounts as $row ) {
                $response = $this->connectAccountService([
                    'type' => $row->type,
                    'access_token' => $row->access_token,
                    'account_no' => $row->account_no
                ]);

                if ($response == false) {
                    Log::info('/portfolio/user_id: Cannot Retrieve Account Information');
                    Log::info($row);
                    continue;
                }

                $row->current_balance = ($row->type == 'UBANK') ? $response['avaiable_balance'] : $response['balance'];
                $total += $row->current_balance;

                $updated_accounts[] = $row;
            }

            $data = [
                'total' => $total,
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

    /**
     * Connect to Account API
     *
     * @param array params
     * @return boolean
     */
    public function connectAccountService($params = [])
    {
        if (empty($params)) {
            return false;
        }
        $accountApi = new AccountApi([
            'type' => $params['type'],
            'access_token' => $params['access_token'],
            'account_no' => $params['account_no']
        ]);

        $response = $accountApi->getAccountInfo();

        return $response;
    }
}
