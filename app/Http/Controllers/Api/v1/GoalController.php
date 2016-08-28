<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\GoalInterface;
use App\Contracts\UserInterface;
use App\Contracts\ContributorInterface;
use App\Contracts\TransactionInterface;

use DB, Log, Exception, Validator, File;

class GoalController extends BaseController
{
    public function __construct(
        GoalInterface $goalInterface,
        UserInterface $userInterface,
        ContributorInterface $contributorInterface,
        TransactionInterface $transactionInterface)
    {
        $this->goal = $goalInterface;
        $this->user = $userInterface;
        $this->contributor = $contributorInterface;
        $this->transaction = $transactionInterface;

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
            $user_id = $request->input('user_id');

            if ($request->input('user_id')) {
                $params['where']['and'][] = [
                    'field' => 'user_id',
                    'value' => $user_id
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

            // Get the goals!
            if (! $goals = $this->goal->getList($params)) {
                throw new Exception("Error Processing Request: Cannot Retrieve Goals");
            }

            $data = [];
            foreach ($goals as $row) {
                $goal_id = $row->id;

                // Get the number of contributors of there is specified user_id
                $contributors = 0;
                if ($user_id) {
                    $param = [
                        'and' => [
                            ['field' => 'goal_id', 'value' => $goal_id]
                        ]
                    ];
                    if (! $contributors = $this->contributor->getTotalBy($param)) {
                        throw new Exception("Error Processing Request: Cannot Retrieve Contributors");
                    }
                }
                $row->contributors = $contributors;

                // Check if the goal is already achieved
                if ($row->status != $this->goal_status['achieved']) {
                    $is_achieved = $this->checkGoalStatus($row);
                    if ($is_achieved) {
                        if (! $this->updateGoalAchievedStatus($goal_id)) {
                            throw new Exception("Error Processing Request: Cannot Update Goal Status");
                        }

                        $row->status = $this->goal_status['achieved'];
                        $row->achieved_date = date('Y-m-d');
                    }
                }

                // Get how many days it took for the goal to achieved
                $days_achieved = 0;
                if ($row->status == $this->goal_status['achieved']) {
                    $achieved_date = strtotime($row->achieved_date);
                    $created_at = strtotime($row->created_at);

                    $diff = $achieved_date - $created_at;
                    $days_achieved = ceil($diff / (60*60*24));
                }

                // Get what percent is the accumulated amount
                $row->accumulated_amount_percentage = ($row->accumulated_amount / $row->target_amount) * 100;;

                $row->days_achieved = $days_achieved;

                $data[] = $row;
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
        Log::info('Goal Store');
        Log::info($request->all());
        
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
        try {
            $isBadRequest = false; // switching of http response code

            // VALIDATION - STARTS
            if (! $this->goal->exists(['id' => $id])) {
                throw new Exception("Error Processing Request: Invalid Goal");
            }
            // VALIDATION - ENDS

            // get the goal info
            $goal = $this->goal->get($id);
            $goal_id = $goal->id;

            // get the contributors and its total amount per contributors
            $params = [
                'selectRaw' => 'users.name, users.facebook_id, sum(amount) total',
                'where' => [
                    'and' => [
                        ['field' => 'goal_id', 'value' => $id]
                    ]
                ],
                'join' => [
                    [
                        'table' => 'users',
                        'one' => 'users.id',
                        'two' => 'transactions.user_id'
                    ]
                ],
                'groupby_raw' => 'user_id, users.name, users.facebook_id'
            ];
            if (! $contributors = $this->transaction->getList($params));

            $data = [
                'goal' => $goal,
                'contributors' => $contributors,
                'total_amount' => $goal->accumulated_amount
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
