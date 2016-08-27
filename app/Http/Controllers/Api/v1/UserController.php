<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface;

use DB, Log, Exception, Validator;

class UserController extends BaseController
{

    public function __construct(
        UserInterface $userInterface)
    {
        $this->user = $userInterface;

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
                $params = [
                    'where' => [
                        'and' => [
                            ['field' => 'id', 'operator' => '!=', 'value' => $request->input('user_id')]
                        ]
                    ]
                ];
            }

            if (! $data = $this->user->getList($params)) {
                throw new Exception("Error Processing Request: Cannot Retrieve Users");
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

            // Validate the request
            $validator = Validator::make($request->all(), [
                'facebook_id' => 'required',
                'name' => 'required',
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email',
                'link' => 'required'
            ]);

            if ($validator->fails()) {
                $isBadRequest = true;
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            /*
             * if facebook id exists, just update the user info
             * else insert user info
             */
            $facebook_id = $request->input('facebook_id');

            $data = [
                'username' => $request->input('link'), // substr($request->input('link'), $start)
                'name' => $request->input('name'),
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname')
            ];

            if ($this->user->exists(['facebook_id' => $facebook_id, 'active' => 1]) == 1) {

                $user = $this->user->getBy(['facebook_id' => $facebook_id, 'active' => 1]);

                if ($this->user->updateBy(['facebook_id' => $facebook_id], $data) == false) {
                    throw new Exception("Error Processing Request: Cannot Update User");
                }

            } else {

                $data['facebook_id'] =  $facebook_id;
                $data['email'] = $request->input('email');

                if (! $user = $this->user->store($data)) {
                    throw new Exception("Error Processing Request: Cannot Add User");
                }
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
            'user_id' => $user->id
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
