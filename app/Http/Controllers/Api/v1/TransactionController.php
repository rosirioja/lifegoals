<?php

namespace App\Http\Controllers\Api1\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\GoalInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserAccountInterface;
use App\Contracts\ContributorInterface;
use App\Contracts\TransactionInterface;

use DB, Log, Exception, Validator;

class TransactionController extends BaseController
{
    public function __construct(
        GoalInterface $goalInterface,
        UserInterface $userInterface,
        UserAccountInterface $userAccountInterface,
        ContributorInterface $contributorInterface,
        TransactionInterface $transactionInterface)
    {
        $this->goal = $goalInterface;
        $this->user = $userInterface;
        $this->userAccount = $userAccountInterface;
        $this->contributor = $contributorInterface;
        $this->transaction = $transactionInterface;

        $this->type = [
            'pending' => 'PENDING',
            'success' => 'SUCCESS',
            'failed' => 'FAILED',
            'cancelled' => 'CANCELLED'
        ];

        DB::enableQueryLog();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $isBadRequest = false; // switching of http response code

            $validator = Validator::make($request->all(), [
                'goal_id' => 'required|numeric',
                'user_id' => 'required|numeric',
                'user_account_id' => 'required|numeric',
                'amount' => 'required|numeric',
                'type' => 'required'
            ]);

            if ($validator->fails()) {
                $isBadRequest = true;
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            $goal_id = $request->input('goal_id');
            $user_id = $request->input('user_id');
            $user_account_id = $request->input('user_account_id');
            $amount = $request->input('amount');

            // VALIDATION - STARTS
            if (! $this->goal->exists(['id' => $goal_id])) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: Invalid Goal");
            }

            if (! $this->user->exists(['id' => $user_id, 'active' => 1])) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: Invalid User");
            }

            if (! $this->userAccount->exists(['id' => $user_account_id])) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: Invalid User Account");
            }

            if (! $this->contributor->exists(['goal_id' => $goal_id, 'user_id' => $user_id])) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: Invalid Contributor");
            }
            // VALIDATION - ENDS

            // Save the transaction
            $data = [
                'goal_id' => $goal_id,
                'user_id' => $user_id,
                'user_account_id' => $user_account_id,
                'amount' => $amount,
                'type' => $request->input('type'),
                'invoice_id' => uniqid('TRN'),
                'transaction_date' => date('Y-m-d'),
                'status' => $this->type['pending']
            ];

            if (! $this->transaction->store($data)) {
                throw new Exception("Error Processing Request: Cannot Add Transaction");
            }

            $userAccount = $this->userAccount->get($user_account_id);

            // Call Transaction Service
            // $response = new TransactionService($userAccount->access_token, $amount);
            // $transaction = $response->fundTransfer($recipient);

        } catch (Exception $e) {
            $code = $isBadRequest ? 400 : 500;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $code);
        }

        return response()->json([
            'success' => true
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
