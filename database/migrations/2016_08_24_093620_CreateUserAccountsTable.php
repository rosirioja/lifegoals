<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->string('type')->default(''); //either UNIONBANK / COINSPH
            $table->string('access_token')->default('');
            $table->string('account_name')->default('');
            $table->string('account_no')->default('');
            $table->string('target_address')->default('');
            $table->double('current_balance', 15, 4)->unsigned()->default(0);
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_accounts', function (Blueprint $table) {
            //
        });
    }
}
