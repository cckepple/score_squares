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

    public function nflgame()
    {
        return $this->belongsTo('App\NflGame', 'nfl_game_id', 'id');
    }

    public function squares()
    {
        return $this->hasMany('App\PoolSquare', 'pool_id', 'id');
    }

    public function unClaimedSquaresCount()
    {
        return $this->squares()->where('status','<',3)->count();
    }

    public function fq_winner(){
        return $this->hasOne('App\User',  'id', 'fq_winner_id');
    }

    public function sq_winner(){
        return $this->hasOne('App\User', 'id', 'sq_winner_id');
    }

    public function tq_winner(){
        return $this->hasOne('App\User', 'id', 'tq_winner_id');
    }

    public function lq_winner(){
        return $this->hasOne('App\User', 'id', 'lq_winner_id');
    }

    public function winners()
    {
        return array(
                '1' => $this->fq_winner,
                '2' => $this->sq_winner,
                '3' => $this->tq_winner,
                '4' => $this->lq_winner,
            );
    }

    public function setScoresSquares($team, $scores)
    {
        if($team === 'home'){
            foreach ($scores as $key => $score) {
                $this->squares()->where('column','=', $key+1)->update(['home_score' => $score]);
            }
        }else{
            foreach ($scores as $key => $score) {
                $this->squares()->where('row','=', $key+1)->update(['away_score' => $score]);
            }
        }
    }
}
