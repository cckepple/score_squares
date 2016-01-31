<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;
class PoolPlayer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pool_players';

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

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function pool()
    {
        return $this->belongsTo('App\Pool', 'pool_id');
    }

    public function totalSquaresClaimed($poolId)
    {
        return PoolSquare::where('user_id', '=', $this->user_id)->where('pool_id','=',$poolId)->count();
    }

    public function totalSquaresBought($poolId)
    {
        return PoolSquare::where('user_id', '=', $this->user_id)->where('pool_id','=',$poolId)->where('status','=',PoolSquare::STATUS_OWNED)->count();
    }

    public function unPaidSquares($poolId)
    {
        return PoolSquare::where('user_id','=', $this->user_id)->where('pool_id','=',$poolId)->where('status','=',PoolSquare::STATUS_PENDING)->get();
    }

    public function paidSquares($poolId)
    {
        return PoolSquare::where('user_id','=', $this->user_id)->where('pool_id','=',$poolId)->where('status','=',PoolSquare::STATUS_OWNED)->get();
    }

    public function allSquares($poolId)
    {
        return PoolSquare::where('user_id','=', $this->user_id)->where('pool_id','=',$poolId)->get();
    }
    public static function openPools($userId)
    {
        
        return PoolPlayer::join('pools', 'pools.id','=','pool_players.pool_id')
                          ->where('pool_players.user_id','=',$userId)
                          ->where('pools.status','=', Pool::STATUS_SQUARES_OPEN)
                          ->get();
        
    }

}

