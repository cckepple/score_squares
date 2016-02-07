@extends('layouts.landing')

@section('content')
@if(Session::has('info'))
  <div class="alert alert-info alert-dismissible text-center">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <p>{{Session::get('info')}}</p>
  </div>
@endif
<div class="register-box" style="margin-top:10px;margin-bottom:0px;">
  <div class="register-logo" style="min-height:20px;"></div>
  <div class="register-box-body">
    <p class="login-box-msg">Super Bowl 50!</p>
    <p class="login-box-msg">Score Quarter <strong>{{$quarter}}</strong> </p>
    <form action="/api/game/score-game/{{$quarter}}/{{$pool->id}}" method="post">
      {{ csrf_field() }}
      <div class="form-group has-feedback">
        <input type="text" class="form-control" id="home_score" name="home_score" placeholder="Panthers Score">
      </div>
      <div class="form-group has-feedback">
        <input type="text" class="form-control" id="away_score" name="away_score" placeholder="Broncos Score">
      </div>
      <div class="row">
        <!-- /.col -->
        <div class="col-xs-12 text-center">
          <button type="submit" class="btn btn-primary btn-block btn-flat">Calculate!</button>
        </div>
        <!-- /.col -->
      </div>
      <input type="hidden" value="{{$quarter}}" name="quater_id">
      <input type="hidden" value="{{$pool->id}}" name="game_id">
    </form>
  </div>
  <!-- /.form-box -->
  <div style="min-height:50px;"></div>
</div>
@stop