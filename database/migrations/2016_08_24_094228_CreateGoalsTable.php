<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->string('name')->default('');
            $table->string('type')->default(''); //personal / group
            $table->string('visibility')->default('public'); // public/private
            $table->double('target_amount', 15, 4)->unsigned()->default(0);
            $table->datetime('target_date');
            $table->double('accumulated_amount', 15, 4)->unsigned()->default(0);
            $table->datetime('achieved_date');
            $table->string('status')->default('ONGOING'); //ONGOING / ACHIEVED
            $table->string('image_path')->default('');
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
        Schema::drop('goals');
    }
}
