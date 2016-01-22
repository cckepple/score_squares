<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'game_players';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'game_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

}

