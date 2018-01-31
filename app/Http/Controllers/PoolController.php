<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Pool;
use App\PoolPlayer;
use App\PoolSquare;
use App\NflGame;
use DB;
use Auth;
use Log;
use Session;

class PoolController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except'=>'show']);
        $this->middleware('game',['only'=>'show']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pool.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            
            Log::info($request->all());

            $honor = $request->input('honor_system');
            Log::info($honor);
            $pool = new Pool();
            $pool->name = $request->input('name');
            $pool->status = Pool::STATUS_SQUARES_OPEN;
            $pool->nfl_game_id = $request->input('nfl_game_id');
            $pool->square_cost = $request->input('square_cost');
            $pool->password = $request->input('password');
            $pool->honor_system = $honor ? 1 : 0;
            $pool->save();

            $poolCreator = new PoolPlayer();
            $poolCreator->user_id = $request->user()->id;
            $poolCreator->pool_id = $pool->id;
            $poolCreator->pool_admin = 1;
            $poolCreator->has_paid = 0;
            $poolCreator->save();

            for ($r=1; $r < 11; $r++) { 
                for ($c=1; $c < 11; $c++) { 
                    $newSquare = new PoolSquare();
                    $newSquare->row = $r;
                    $newSquare->column = $c;
                    $newSquare->status = 1; //figure out statuses
                    $newSquare->pool_id = $pool->id;
                    $newSquare->save();
                }
                
            }

            return redirect()->action('PoolController@show', [$pool->id]);
        } catch (Exception $e) {
            
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $pool = Pool::find($id);
        if (PoolPlayer::inGame($request->user()->id, $pool->id)) {
            return view('pool.show')->with(array('pool'=>$pool));
        }else{
            Log::info('show game PW screen');
            return view('pool.password')->with(array('pool'=>$pool));
        }
    }

    public function join(Request $request)
    {
        $pool = Pool::find($request->input('poolId'));
        if ($pool->password == $request->input('password')) {
            $player = new PoolPlayer();
            $player->pool_id = $pool->id;
            $player->user_id = $request->user()->id;
            $player->pool_admin = 0;
            $player->has_paid = 0;
            $player->save();
            return redirect()->action('PoolController@show', [$pool->id]);

        }else{
            Session::flash('info','Incorrect Password, please try again.');
            return redirect()->back();
        }

    }

    public function getPoolSquares($id)
    {   
        $pool = Pool::find($id);
        $homeScores = explode('-', $pool->home_scores);
        $awayScores = explode('-', $pool->away_scores);
        try {
            Log::info($pool->nflgame->homeTeam);
        } catch (Exception $e) {
            Log::info($e);
        }
        $data = array(
                        'squares'=>PoolSquare::with('user')->where('pool_id','=', $id)->get(), 
                        'curUser'=>Auth::user()->id, 
                        'gameInfo'=>$pool, 
                        'nflGameInfo'=> $pool->nflgame,
                        'winners' => $pool->winners(),
                        'admin'=>PoolPlayer::poolAdmin($id),
                        'homeScores'=>$homeScores,
                        'awayScores'=>$awayScores,
                        'homeTeam'=>$pool->nflgame->homeTeam,
                        'awayTeam'=>$pool->nflgame->awayTeam
                    );

        return response()->json($data);
    }

    public function purchaseSquare($id)
    {
        try {   
            $square = PoolSquare::findOrFail($id);
            // use a transaction to prevent any issues
            // from concurrent modifications
            DB::transaction(function() use($square) {
                $thisSquare = $square->newQuery()
                    ->lockForUpdate()
                    ->find($square->id, ['id', 'status']);
                if ($thisSquare->status['id'] == PoolSquare::STATUS_OPEN) {
                    $curUser = Auth::user()->id;
                    $thisSquare->status = PoolSquare::STATUS_PENDING;
                    $thisSquare->user_id = $curUser;
                    $thisSquare->save();

                    $hasPlayeRecord = PoolPlayer::where('user_id','=',$curUser)->where('pool_id','=',$square->pool_id)->count();
                    if (!$hasPlayeRecord) {
                        $poolCreator = new PoolPlayer();
                        $poolCreator->user_id = $curUser;
                        $poolCreator->pool_id = $square->pool_id;
                        $poolCreator->pool_admin = 0;
                        $poolCreator->has_paid = 0;
                        $poolCreator->save();
                    }
                    
                    return response()->json('success');
                }else{
                    return response()->json('square not available');
                }
            });
            
        } catch (Exception $e) {
            return response()->json($e);
        }
    }

    public function getPoolPlayers(Request $request,$id)
    {
        try {
            $players = PoolPlayer::with('user')->where('pool_id','=',$id)->get();
            foreach ($players as $player) {
                $player->totalSquareCount = $player->totalSquaresClaimed($id);
                $player->paidSquareCount = $player->totalSquaresBought($id);
                $player->oweSquareCount = $player->totalSquareCount - $player->paidSquareCount;
            }
            return response()->json($players);
        } catch (Exception $e) {
            return response()->json(array('error'=>true,'msg'=>$e));
        }
        
    }

    public function playerPaid(Request $request)
    {
        $data = $request->input('poolPlayer');
        $player = PoolPlayer::find($data['id']);
        $squares = $player->unPaidSquares($data['poolId']);
        PoolSquare::claimSquares($squares, $data['paidUp']);
        return response()->json('success');
    }

    public function removePlayerPaid(Request $request)
    {
        $data = $request->input('poolPlayer');
        $player = PoolPlayer::find($data['id']);
        $squares = $player->paidSquares($data['poolId']);
        if($squares){
            PoolSquare::unPaySquares($squares, $data['paidDown']);
        }
        return response()->json('success');
    }

    public function removePlayerClaim(Request $request)
    {
        $data = $request->input('poolPlayer');
        $player = PoolPlayer::find($data['id']);
        $squares = $player->unPaidSquares($data['poolId']);
        if($squares){
            PoolSquare::unClaimSquares($squares, $data['claimDown']);
        }
        return response()->json('success');
    }

    public function scoreGameShow($quarter,$gameId)
    {
        if ($quarter > 4) {
            $quarter = 4;
        }
        $data = array('quarter'=>$quarter, 'pool'=>$gameId);
        return view('pool.score-game')->with($data);
    }
    public function scoreGamePost(Request $request)
    {
        $homeScore = $request->input('home_score');
        $awayScore = $request->input('away_score');
        $gameId = $request->input('gameId');
        $quarter = $request->input('quarter');

        switch ($quarter) {
            case '1':
                $setWinner = 'fq_';
                break;
            case '2':
                $setWinner = 'sq_';
                break;
            case '3':
                $setWinner = 'tq_';
                break;
            case '4':
                $setWinner = 'lq_';
                break;
            default:
                $setWinner = false;
                break;
        }


        if($setWinner){
            NflGame::where('id','=',$gameId)->update([$setWinner.'home_score' => $homeScore, $setWinner.'away_score' => $awayScore]);

            $homeScore = substr($homeScore, -1);
            $awayScore = substr($awayScore, -1);

            PoolSquare::where('home_score','=',$homeScore)->where('away_score','=',$awayScore)->update(array('status'=>PoolSquare::STATUS_WINNER));
            $winningSquares = PoolSquare::where('home_score','=',$homeScore)->where('away_score','=',$awayScore)->get();
            foreach ($winningSquares as $square) {
                Pool::where('id','=',$square->pool_id)->update(array($setWinner.'winner_id'=>$square->user_id));
            }

            Session::flash('info','Score Was Saved! -- Pats:'.$homeScore.' | Atl: '.$awayScore);
            $data = array('quarter'=>$quarter, 'pool'=>$gameId);
        }
        return view('pool.score-game')->with($data);
    }

    public function testSetScore($gameId)
    {
        $pool = Pool::find($gameId);
        if($pool->unClaimedSquaresCount()){
            /* Squares are still available, don't allow */
            return response()->json('squares available');
        }else{

            $scores = [0,1,2,3,4,5,6,7,8,9];
            
            shuffle($scores);
            $pool->home_scores = implode("-", $scores);
            $pool->setScoresSquares('home',$scores);
            
            shuffle($scores);
            $pool->away_scores = implode("-", $scores);
            $pool->setScoresSquares('away',$scores);
            
            $pool->status = $pool::STATUS_PRE_GAME;
            $pool->save();


            return response()->json('scores set!');

        }

    }
}
