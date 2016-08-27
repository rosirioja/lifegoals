<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('goal_id')->unsigned()->default(0);
            $table->integer('user_id')->unsigned()->default(0);
            $table->integer('user_account_id')->unsigned()->default(0);
            $table->double('amount', 15, 4)->unsigned()->default(0);
            $table->string('type')->default(''); //CASHIN / CASHOUT
            $table->string('invoice_id')->default('');
            $table->string('reference_no')->default('');
            $table->datetime('transaction_date')->nullable();
            $table->string('status')->default(''); // PENDING / SUCCESS / FAILED / CANCELLED
            $table->timestamps();
            $table->foreign('goal_id')->references('id')->on('goals');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('user_account_id')->references('id')->on('user_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
}
