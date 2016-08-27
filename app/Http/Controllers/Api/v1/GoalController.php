<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\GoalInterface;
use App\Contracts\UserInterface;
use App\Contracts\ContributorInterface;

use DB, Log, Exception, Validator, File;

class GoalController extends BaseController
{
    public function __construct(
        GoalInterface $goalInterface,
        UserInterface $userInterface,
        ContributorInterface $contributorInterface)
    {
        $this->goal = $goalInterface;
        $this->user = $userInterface;
        $this->contributor = $contributorInterface;

        $this->goal_type = [
            'personal' => 'PERSONAL',
            'group' => 'GROUP'
        ];

        DB::enableQueryLog();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $params = [];

            if ($request->input('user_id')) {
                $params['where']['and'][] = [
                    'field' => 'user_id',
                    'value' => $request->input('user_id')
                ];
            }

            if ($request->input('status')) {
                $params['where']['and'][] = [
                    'field' => 'status',
                    'value' => $request->input('status')
                ];
            }

            if ($request->input('visibility')) {
                $params['where']['and'][] = [
                    'field' => 'visibility',
                    'value' => $request->input('visibility')
                ];
            }

            if (! $data = $this->goal->getList($params)) {
                throw new Exception("Error Processing Request: Cannot Retrieve Goals");
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
                'user_id' => 'required|numeric',
                'name' => 'required',
                'visibility' => 'required',
                'target_amount' => 'required|numeric',
                'target_date' => 'date',
            ]);

            if ($validator->fails()) {
                $isBadRequest = true;
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            $user_id = $request->input('user_id');

            // VALIDATION - STARTS
            if (! $this->user->exists(['id' => $user_id, 'active' => 1])) {
                $isBadRequest = true;
                throw new Exception("Error Processing Request: Invalid User");
            }
            // VALIDATION - ENDS

            /**
             * Insert into goals first
             * Then into contributors
             */

            $data = [
                'user_id' => $user_id,
                'name' => $request->input('name'),
                'type' => $this->goal_type['personal'],
                'visibility' => $request->input('visibility'),
                'target_amount' => $request->input('target_amount'),
                'target_date' => $request->input('target_date')
            ];

            // Upload Image!
            if ($request->hasFile('image')) {
                $path = 'uploads/images/'. $user_id .'/';
                $destinationPath = public_path($path);

                // Check if the file is valid
                if (! $request->file('image')->isValid()) {
                    throw new Exception("Error Processing Request: Cannot Upload Image");
                }

                if (! File::exists($destinationPath))
                {
                    File::makeDirectory($destinationPath, 0775, true);
                }

                $filename = $request->file('image')->getClientOriginalName();
                $extension = $request->file('image')->getClientOriginalExtension();
                $newFilename = $request->file('image')->getFilename() .'.'. $extension;

                // uploading file to given path
                if(! $request->file('image')->move($destinationPath, $newFilename)) {
                    throw new Exception("Error Processing Request: Cannot Upload Image");
                }

                $data['image_path'] = $path.$newFilename;
            }

            // Insert in DB!
            if (! $goal = $this->goal->store($data)) {
                throw new Exception("Error Processing Request: Cannot Add Goal");
            }

            $data = [
                'goal_id' => $goal->id,
                'user_id' => $user_id
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
