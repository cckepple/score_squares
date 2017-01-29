<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nfl_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('abbreviaion');
            $table->string('primary_color');
            $table->string('secondary_color');
            $table->string('primary_logo_path');
            $table->integer('wins')->unsinged();
            $table->integer('losses')->unsinged();
            
        });

         Schema::create('nfl_games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('home_team_id')->unsigned();
            $table->integer('away_team_id')->unsigned();
            $table->timestamp('game_time');

            $table->integer('fq_home_score')->unsigned()->default(0);
            $table->integer('sq_home_score')->unsigned()->default(0);
            $table->integer('tq_home_score')->unsigned()->default(0);
            $table->integer('lq_home_score')->unsigned()->default(0);

            $table->integer('fq_away_score')->unsigned()->default(0);
            $table->integer('sq_away_score')->unsigned()->default(0);
            $table->integer('tq_away_score')->unsigned()->default(0);
            $table->integer('lq_away_score')->unsigned()->default(0);

            $table->foreign('home_team_id')->references('id')->on('nfl_teams');
            $table->foreign('away_team_id')->references('id')->on('nfl_teams');
        });

        Schema::create('pools', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nfl_game_id')->unsigned();
            $table->string('name');
            $table->string('password', 60);
            $table->string('square_cost');
            $table->boolean('honor_system');
            $table->tinyInteger('status')->unsigned();
            $table->integer('fq_winner_id')->unsigned()->nullable();
            $table->integer('sq_winner_id')->unsigned()->nullable();
            $table->integer('tq_winner_id')->unsigned()->nullable();
            $table->integer('lq_winner_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('nfl_game_id')->references('id')->on('nfl_games');
            $table->foreign('fq_winner_id')->references('id')->on('users');
            $table->foreign('sq_winner_id')->references('id')->on('users');
            $table->foreign('tq_winner_id')->references('id')->on('users');
            $table->foreign('lq_winner_id')->references('id')->on('users');
        });

        Schema::create('pool_players', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('pool_id')->unsigned();
            $table->boolean('pool_admin');
            $table->boolean('has_paid');
            $table->tinyInteger('quater_wins')->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('pool_id')->references('id')->on('pools');
        });

        Schema::create('pool_squares', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('row')->unsigned();
            $table->integer('column')->unsigned();
            $table->integer('status')->unsigned();
            $table->integer('pool_id')->unsigned();
            $table->integer('user_id')->nullable()->unsigned();

            $table->integer('fq_score_home')->unsigned()->nullable();
            $table->integer('sq_score_home')->unsigned()->nullable();
            $table->integer('tq_score_home')->unsigned()->nullable();
            $table->integer('lq_score_home')->unsigned()->nullable();
            $table->integer('fq_score_away')->unsigned()->nullable();
            $table->integer('sq_score_away')->unsigned()->nullable();
            $table->integer('tq_score_away')->unsigned()->nullable();
            $table->integer('lq_score_away')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('pool_id')->references('id')->on('pools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pool_players');
        Schema::dropIfExists('pool_squares');
        Schema::dropIfExists('pools');
        Schema::dropIfExists('nfl_games');
        Schema::dropIfExists('nfl_teams');
    }
}
