@extends('layouts.master')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Create New Pool
    <!-- <small>Optional description</small> -->
  </h1>
  <!-- <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
    <li class="active">Here</li>
  </ol> -->
</section>

<!-- Main content -->
<section class="content">
  	<div class="row">
  		<div class="col-lg-6 col-lg-offset-3">
			<div class="box box-default">
				<div class="box-header with-border">
					<!-- <h3 class="box-title">Quick Example</h3> -->
				</div>
				<!-- form start -->
				{!! Form::open(['action' => 'PoolController@store']) !!}
				  	<div class="box-body">
				  		<div class="form-group col-lg-12">
					      <label>Pool Name</label>
					      <input type="name" class="form-control" name="name" placeholder="Name">
					    </div>
					    <div class="form-group col-lg-3">
					      <label>Choose Week</label>
					      <select class="form-control" name="game_week" id="game_week">
		                    <option>Super Bowl</option>
		                  </select>
					    </div>
					    <div class="form-group col-lg-9">
					      <label>Choose Game</label>
					      <!-- show games available for chosen week  -->
					      <select class="form-control" name="nfl_game_id">
					      	<option value="2">Patriots vs Falcons</option>
					      </select>
					    </div>
					    <div class="form-group col-lg-4">
					      <label>Cost Per Square</label>
					      <div class="input-group">
			                <span class="input-group-addon">$</span>
			                <input type="text" class="form-control" name="square_cost">
			              </div>
					    </div>
					    <div class="form-group col-lg-8">
					      <label>Game Password</label>
					      <input type="password" class="form-control" name="password" placeholder="Password">
					    </div>
					    <!-- <div class="checkbox form-group col-lg-12">
					      <label>
					        <input type="checkbox" name="honor_system"> Honor System
					      </label>
					      	<p class="help-block">
			                	This allows square purchases to automatically finalize.  
			                	Without this, each square purchase requires the game creator to verify
			                	that the money has been received.
							</p>
					    </div> -->


					   <!--  <div class="form-group">
					      <label for="exampleInputFile">Game Pass</label>
					      <input type="file" id="exampleInputFile">

					      <p class="help-block">Example block-level help text here.</p>
					    </div> -->
				  	</div>

					<div class="box-footer">
						<button type="submit" class="btn btn-primary btn-app pull-right" style="background-color:#3c8dbc;color:white;padding:10px 5px;">
		                	<i class="flaticon-americanfootball3" style="display:block;font-size:20px;width:50px !important"></i> Create
		              	</button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</section><!-- /.content -->
@stop
