<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pool extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pools';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nfl_game_id', 'password', 'square_cost', 'honor_system', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    const STATUS_SQUARES_OPEN = 1;
    const STATUS_PRE_GAME = 2;
    const STATUS_GAME_TIME = 3;
    const STATUS_POST_GAME = 4;
}
