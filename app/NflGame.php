<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NflGame extends Model
{
    public $timestamps = false;

    public function pools()
    {
    	return $this->hasMany('App\Pool', 'nfl_game_id', 'id');
    }
}
