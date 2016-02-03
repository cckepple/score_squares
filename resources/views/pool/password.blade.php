@extends('layouts.master')

@section('content')
<section class="content-header">
  <h1>
    Join Pool -- {{$pool->name}}
  </h1>
</section>

<section class="content">
  	<div class="row">
  		<div class="col-lg-6 col-lg-offset-3">

			<div class="box box-default">
				<div class="box-header with-border">
				</div>
				{!! Form::open(['action' => 'PoolController@join']) !!}
				  	<div class="box-body">
				  		<div class="form-group col-lg-12">
					      <label>Pool Password</label>
					      <input type="password" class="form-control" name="password" placeholder="Password">
					    </div>
				  	</div>

					<div class="box-footer">
						<button type="submit" class="btn btn-primary btn-app pull-right" style="background-color:#3c8dbc;color:white;padding:10px 5px;">
		                	<i class="flaticon-americanfootball3" style="display:block;font-size:20px;width:50px !important"></i> Join
		              	</button>
					</div>
					<input type="hidden" value="{{$pool->id}}" name="poolId">
				{!! Form::close() !!}
			</div>
			@if(Session::has('info'))
			  <div class="alert alert-danger alert-dismissible">
			      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			      <p>{{Session::get('info')}}</p>
			  </div>
			@endif
		</div>
	</div>
</section>
@stop
