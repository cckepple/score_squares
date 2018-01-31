<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NflGame extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nfl_games';
    
    public $timestamps = false;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['home_team_id', 'away_team_id'];

    public function pools()
    {
    	return $this->hasMany('App\Pool', 'nfl_game_id', 'id');
    }

    public function homeTeam(){
    	return $this->hasOne('App\NflTeam', 'id', 'home_team_id');
    }

    public function awayTeam(){
    	return $this->hasOne('App\NflTeam', 'id', 'away_team_id');
    }
}
