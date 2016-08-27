<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function(){

    Route::resource('users', 'UserController');

    Route::post('accounts', 'UserAccountController@getAccounts');
    Route::get('portfolio/{user_id?}', 'UserAccountController@getPortfolio');

    Route::resource('goals', 'GoalController');

    Route::resource('contributors', 'ContributorController');

    Route::resource('transactions', 'TransactionController');
});
