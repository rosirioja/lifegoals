<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface;
use App\Contracts\ContributorInterface;

use DB, Log, Exception;

class ContributorController extends BaseController
{
    public function __construct(
        UserInterface $userInterface,
        ContributorInterface $contributorInterface)
    {
        $this->user = $userInterface;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        //
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
