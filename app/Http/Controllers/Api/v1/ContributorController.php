<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface;
use App\Contracts\GoalInterface;
use App\Contracts\ContributorInterface;

use DB, Log, Exception, Validator;

class ContributorController extends BaseController
{
    public function __construct(
        UserInterface $userInterface,
        GoalInterface $goalInterface,
        ContributorInterface $contributorInterface)
    {
        $this->user = $userInterface;
        $this->goal = $goalInterface;
        $this->contributor = $contributorInterface;

        DB::enableQueryLog();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            if (! $data = $this->contributor->getList([
                'join' => [
                    [
                        'table' => 'users',
                        'one' => 'users.id',
                        'two' => 'contributors.user_id'
                    ]
                ]
            ])) {
                throw new Exception("Error Processing Request: Cannot Retrieve Contributors");
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);

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
                'contributor_id' => 'required|numeric', // also a user_id, but in context a contributor
                'goal_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                $isBadRequest = true;
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            $contributor_id = $request->input('contributor_id');
            $goal_id = $request->input('goal_id');

            // VALIDATION - STARTS
            if (! $this->user->exists(['id' => $contributor_id, 'active' => 1])) {
                throw new Exception("Error Processing Request: Invalid Contributor");
            }

            if (! $this->goal->exists(['id' => $goal_id])) {
                throw new Exception("Error Processing Request: Invalid Goal");
            }

            // Check if already exists
            if ($this->contributor->exists(['user_id' => $contributor_id, 'goal_id' => $goal_id])) {
                throw new Exception("Error Processing Request: Contributor is already added");
            }
            // VALIDATION - ENDS

            $data = [
                'user_id' => $contributor_id,
                'goal_id' => $goal_id
            ];

            if (! $this->contributor->store($data)) {
                throw new Exception("Error Processing Request: Cannot Add Contributor");
            }

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
