@extends('layouts.landing')

@section('content')
<div class="landing">
    <!-- <div style="background:rgba(85, 95, 86, 0.6);min-height:1200px;"> -->
        <div class="row">
        <!-- <div class="tint"> -->
          <div class="col-sm-4 col-sm-offset-4 text-center">
            <div class="logo-lg landing-logo" style="font-size:90px;color:#black;text-shadow: 2px 0 white, 0 2px white, 2px 0 white, 0 -2px white;margin-top:20px;"><b>Score</b>Squares</div>
            <!-- <div class="landing-text" style="font-size:25px;color:black;text-shadow: 1px 0 white, 0 2px white, 2px 0 white, 0 -1px white;min-width:530px;">Managing your game day pool is easier than ever!</div> -->
            <br>
            <div class="box box-info landing-form" style="min-width:530px;">
            <div class="box-header with-border">
              <h3 class="box-title">Login</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form class="form-horizontal" action="/login" method="post"> 
              {{ csrf_field() }}
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Email</label>
                  <div class="col-sm-10">
                    <input type="email" class="form-control" name="email" id="inputEmail3" placeholder="Email">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Password</label>
                  <div class="col-sm-10">
                    <input type="password" class="form-control" name="password" id="inputPassword3" placeholder="Password">
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-8">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="remember" checked> Keep me logged in.
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-5 col-sm-offset-1" style="margin-top:5px;">
                </div>
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-info pull-right">Sign in</button>
                  <a href="/register" id="newUserBtn" class="btn btn-default pull-right" style="margin-right:5px;">Register</a>
                </div>
              </div>
            </form>
            </div>
            @if (count($errors) > 0)
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
                
          </div>
<!--           <div class="col-sm-6">
            <img src="img/ss_trophy.png" class="trophy" style="max-height:580px;max-width:580px;margin-left:40%">
          </div> -->
        </div>
        <div class="row">
        </div>
    <!-- </div> -->
</div>
@stop

@section('bottomscript')

@stop