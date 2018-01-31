<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NflTeam extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nfl_teams';
    
    public $timestamps = false;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
