<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToActuallyScoreGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('pools', function (Blueprint $table) {
            $table->string('home_scores');
            $table->string('away_scores');
        });

       Schema::table('pool_squares', function (Blueprint $table) {
            $table->integer('home_score')->unsigned();
            $table->integer('away_score')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
