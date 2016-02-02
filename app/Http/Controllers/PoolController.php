<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Pool;
use App\PoolPlayer;
use App\PoolSquare;
use DB;
use Auth;
use Log;

class PoolController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            
            
            $pool = new Pool();
            $pool->name = $request->input('name');
            $pool->status = Pool::STATUS_SQUARES_OPEN;
            $pool->nfl_game_id = $request->input('nfl_game_id');
            $pool->square_cost = $request->input('square_cost');
            $pool->password = $request->input('password');
            $pool->honor_system = $request->input('honor_system');
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
    public function show($id)
    {
        $pool = Pool::find($id);
        return view('pool.show')->with(array('pool'=>$pool));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getPoolSquares($id)
    {
        return response()->json(array('squares'=>PoolSquare::where('pool_id','=', $id)->get(), 'curUser'=>Auth::user()->id, 'gameInfo'=>Pool::find($id), 'admin'=>PoolPlayer::poolAdmin($id)));
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
        Log::info($data);
        $player = PoolPlayer::find($data['id']);
        $squares = $player->unPaidSquares($data['poolId']);
        PoolSquare::claimSquares($squares, $data['paidUp']);
        return response()->json('success');
    }

    public function removePlayerPaid(Request $request)
    {
        $data = $request->input('poolPlayer');
        $player = PoolPlayer::find($data['id']);
        Log::info($data);
        if ($data['holdClaim']) {
            $squares = $player->allSquares($data['poolId']);
        }else{
            $squares = $player->paidSquares($data['poolId']);
        }

        if($squares){
            PoolSquare::unClaimSquares($squares, $data['paidDown']);
        }
        return response()->json('success');
    }
}
