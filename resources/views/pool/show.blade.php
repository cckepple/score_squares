@extends('layouts.master')
@section('topscript')
<style type="text/css">
	.disabled {
		opacity: .65;
	}
	.den-text{
		position:relative; 
		top:185px; 
		left:50px;
		font-weight:bold;
		font-size:1.2em
	}

	.den-image{
		max-width:100px;
		max-height:100px;
		position:relative;
		top:150px;
		right:30px;
	}

	.car-text{
		font-size:1.2em;
		position:relative;
		top:30px;
		font-weight:bold;
		left:25px;
	}
	.car-image{
		max-width:100px;
		max-height:100px;
		position:relative;
		top:20px;
		left:40px;
	}
	@media only screen and (max-width: 767px) {
	    .square-table {
	        margin-left: 50px;
	    }
	    .den-text{
	    	font-size:1em;
	    	left:10px;
	    	top:125px;
	    }
	    .den-image{
	    	max-width: 50px;
	    	max-height: 50px;
	    	right:40px;
	    	top:100px;
	    }
	    .car-text{
			font-size:1em;
			top:10px;
			left:5px;
	    }
	    .car-image{
	    	max-width: 50px;
	    	max-height: 50px;
	    	top:5px;
	    	left:5px;
	    }
	}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.4.8/angular-route.js"></script>
<script src="/js/ng/ui-bootstrap-custom-tpls-1.1.0.min.js"></script>
<script src="/js/clipboard.min.js"></script>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
	   	new Clipboard('#copyLink');
	});
</script>
<script src="/js/ng/squareapp.js"></script>
<script type="text/javascript">
app.controller('ShowPoolCtrl', function ($scope, $http, $filter, $location, $timeout, $window, $uibModal, SettingsService) {		
	$scope.getPoolSquares = function() 
	{
		$http.get('/api/pool/'+$scope.poolId+'/squares').success(function(data){
			var squares = data.squares;
			$scope.myId = data.curUser;
			$scope.gameInfo = data.gameInfo;
			console.log('**');
			console.log($scope.gameInfo);
			$scope.grid = []
			var i = 0;
			for (var r = 0; r < 10; r++) {
				$scope.grid.push({'row':r+1, 'slots':[]});
				var currentRow = $scope.grid[r];
				for (var c = 10; c > 0; c--) {
					squares[i].active = false;
					if (squares[i].user_id == $scope.myId && squares[i].status.id == 3) {
						squares[i].mySquare = true;
					}
					currentRow.slots.push(squares[i]);
					i++;
				};
			};
			$scope.makingPurchase = false;
			$scope.pageLoading = false;
		});
	}

	$scope.getPoolPlayers = function() 
	{
		$http.get('/api/pool/'+$scope.poolId+'/players').success(function(data){
			$scope.players = data;
			console.log($scope.players)
		});
	}

	$scope.selectSlot = function(slot) 
	{
		slot.active = true;
		$scope.openModal(slot);
	}

	$scope.openModal = function (slot) 
	{

		var modalInstance = $uibModal.open({
		  animation: true,
		  templateUrl: 'buy-square.html',
		  controller: 'ModalInstanceCtrl',
		  size: 'md',
		  resolve: {
	        slot: function () {
	          return slot;
	        },
	        squareCost: function() {
	        	return $scope.gameInfo.square_cost;
	        }
	      }
		});

		modalInstance.result.then(function (squareName, squareCost) {
		  $scope.makingPurchase = true;
		  $scope.purchaseSlot(slot);
		}, function () {
		  slot.active = false;
		  console.log('Modal dismissed at: ' + new Date());
	  	});
	};

	$scope.markPaid = function (player) 
	{

		var modalInstance = $uibModal.open({
		  animation: true,
		  templateUrl: 'mark-paid.html',
		  controller: 'PaidModalInstanceCtrl',
		  size: 'md',
		  resolve: {
	        player: function() {
	        	return player;
	        },
	       	squareCost: function() {
	        	return $scope.gameInfo.square_cost;
	        }
	      }
		});

		modalInstance.result.then(function (selectedPlayer) {
			console.log(selectedPlayer);
			$scope.playerPay(selectedPlayer);
		}, function () {
		  console.log('Modal dismissed at: ' + new Date());
	  	});
	};

	$scope.purchaseSlot = function(slot)
	{
		$http.get('/api/square/'+slot.id+'/purchase').success(function(data){
			$scope.getPoolSquares();
			$scope.getPoolPlayers();
		}).error(function(data) {
			console.log(data);
		});
	}

	$scope.playerPay = function(player)
	{
		var poolPlayer = {'id':player.id,'poolId':player.pool_id,paidUp:player.paidUp};
		$http.post('/api/pool/payer-paid', {poolPlayer}).success(function(data){
			$scope.getPoolSquares();
			$scope.getPoolPlayers();
		}).error(function(data){

		});
	}

	$scope.showDiv = function(divId)
	{
		angular.forEach($scope.divs, function(div){
			if (div.id == divId) {
				div.active = true;
			}else{
				div.active = false;
			}
		});
	}

	$scope.absUrl = $location.absUrl();
	$scope.niceUrl = $location.absUrl().substr(7);
	$scope.poolId = $location.absUrl().substr(27);
	$scope.letters = ['A','B','C','D','E','F','G','H','I','J'];
	$scope.divs  = [{'name':'squares','id':1,'active':true},{'name':'players','id':2,'active':false},{'name':'board','id':3,'active':false},{'name':'admind','id':4,'active':false}]
	$scope.makingPurchase = false;
	$scope.pageLoading = true;
	$scope.getPoolSquares();
	$scope.getPoolPlayers();
});

app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance, slot, squareCost) {
  $scope.squareCost = squareCost;
  $scope.squareName = String.fromCharCode(64 + parseInt(slot.column))+'-'+slot.row;

  $scope.ok = function () {
    $uibModalInstance.close($scope.squareName, $scope.squareCost);
  };

  $scope.cancel = function () {
    $uibModalInstance.dismiss('cancel');
  };
});

app.controller('PaidModalInstanceCtrl', function ($scope, $uibModalInstance, player, squareCost) {
	$scope.selectedPlayer = player;
	$scope.totalPaid = 0;
	$scope.squaresPaid = 0;
	$scope.owedBalance = (player.oweSquareCount  * squareCost)

	$scope.ok = function () {
	  $uibModalInstance.close($scope.selectedPlayer, $scope.squaresPaid);
	};

	$scope.cancel = function () {
	  $uibModalInstance.dismiss('cancel');
	};

	$scope.watchBalance = function()
	{

		if($scope.selectedPlayer.paidUp > player.oweSquareCount){
			$scope.selectedPlayer.paidUp = player.oweSquareCount;
		}
		$scope.totalPaid = $scope.selectedPlayer.paidUp * squareCost;
		$scope.owedBalance = ($scope.selectedPlayer.oweSquareCount  * squareCost) - $scope.totalPaid;
	}
});

</script>
@stop

@section('content')
<div ng-app="scoreSquares" ng-controller="ShowPoolCtrl">
	<section class="content-header">
	  	<h4>SUPER BOWL 50 - Pool Name</h4>
	</section>
	<section class="content">
	  	<div class="row">
	  		<div class="col-lg-12">
				<div class="box box-default">
					<div class="box-body ng-cloak" style="overflow-x:auto" ng-hide="pageLoading">
					  	<div class="row">
					  		<div class="col-sm-12">
						  		<div class="btn-group text-center" role="group">
									<button class="btn btn-primary" ng-class="{'active': divs[0].active}" ng-click="showDiv(1)">Squares</button>
									<button class="btn btn-primary" ng-class="{'active': divs[1].active}" ng-click="showDiv(2)">Scores</button>
									<button class="btn btn-primary" ng-class="{'active': divs[2].active}" ng-click="showDiv(3)">Admin</button>
								</div>	
						  	</div>
					  	</div>
					  	<hr>
					  	<div class="row" ng-show="divs[0].active">
						  	<div style="float:left">
						  		<span class="den-text">Broncos</span>
						  		<img src="/img/team_logos/broncos.png" class="den-image">
						  	</div>
						  	<div class="col-sm-9">
						  		<img src="/img/team_logos/carolina.png" class="car-image">
						  		<span class="car-text">Panthers</span>
							    <table class="table square-table">
							    	<tr style="border-left:5px solid black;border-right:1px solid #f4f4f4;">
							    		<td style="border-top:5px solid black;"></td>
							    		<td ng-repeat="letter in letters" style="border-top:5px solid #0088CE">[[letter]]</td>
							    	</tr>
							    	<tr ng-repeat="row in grid">
							    		<td style="border-left:5px solid #FB4F14;width:1px;">[[row.row]]</td>
							    		<td ng-click="selectSlot(slot)" ng-class="{'info':slot.active,'bg-gray':slot.status.id == 2,'bg-green':slot.mySquare, 'bg-red disabled':slot.status.id == 3}" ng-repeat="slot in row.slots" style="height:80px;width:80px;border:grey solid 1px;cursor:pointer;">
							    			<i ng-show="makingPurchase && slot.active" class="fa fa-circle-o-notch fa-spin"></i>
							    			<div ng-hide="makingPurchase && slot.active">[[slot.status.name]]</div>
							    			<div ng-show="slot.status.id == 3">[[slot.user_id]]</div>
							    			[[slot.mySquare]]
							    		</td>
							    	</tr>
							    </table>
							</div>
						</div>
						<div ng-show="divs[1].active">
							<h4>Score Board</h4>
							<div class="col-md-3 col-sm-6 col-xs-12">
					          <div class="info-box">
					            <span class="info-box-icon bg-green"><i class="fa fa-flag-o"></i></span>
					            <div class="info-box-content">
					              <span class="info-box-text">1st Quarter</span>
					              <span class="info-box-number">410</span>
					            </div>
					          </div>
					        </div>
					        <div class="col-md-3 col-sm-6 col-xs-12">
					          <div class="info-box">
					            <span class="info-box-icon bg-green"><i class="fa fa-flag-o"></i></span>

					            <div class="info-box-content">
					              <span class="info-box-text">2nd Quarter</span>
					              <span class="info-box-number">410</span>
					            </div>
					          </div>
					        </div>
					        <div class="col-md-3 col-sm-6 col-xs-12">
					          <div class="info-box">
					            <span class="info-box-icon bg-green"><i class="fa fa-flag-o"></i></span>
					            <div class="info-box-content">
					              <span class="info-box-text">3rd Quarter</span>
					              <span class="info-box-number">410</span>
					            </div>
					          </div>
					        </div>
					        <div class="col-md-3 col-sm-6 col-xs-12">
					          <div class="info-box">
					            <span class="info-box-icon bg-green"><i class="fa fa-flag-o"></i></span>
					            <div class="info-box-content">
					              <span class="info-box-text">4th Quarter</span>
					              <span class="info-box-number">410</span>
					            </div>
					          </div>
					        </div>
						</div>
						<div ng-show="divs[2].active">
							<div class="col-sm-5" style="margin-bottom:15px;">
								<h4>Current Players</h4>
								<div class="row" ng-repeat="player in players" style="border-bottom:1px solid #C0C0C0;padding-bottom:10px;padding-top:10px;">
									<div class="col-xs-6">
										<div><strong>[[player.user.email]]</strong></div>
											<div>
												Squares Claimed - 
												<span style="font-size:1.1em;font-weight:bold;">
													[[player.totalSquareCount]]
												</span>
											</div>
											<div>
												Paid -
												<span style="font-size:1.1em;font-weight:bold;color:#00a65a">
													[[player.paidSquareCount * gameInfo.square_cost | currency]] 
												</span>
											</div>
											<div ng-show="player.oweSquareCount > 0">
												Owes - 
												<span style="font-size:1.1em;font-weight:bold;color:#d73925">
													[[player.oweSquareCount * gameInfo.square_cost | currency]]
												</span>
											</div>
									</div>
									<div class="col-xs-6">
										<div ng-show="player.oweSquareCount > 0" class="pull-right"><button ng-click="markPaid(player)" class="btn btn-success btn-app" style="margin-top:5px;background-color:#d73925;color:white;"><i class="fa fa-money"></i> Mark Paid</button></div>
										<div ng-hide="player.oweSquareCount > 0" class="pull-right"><button ng-click="markPaid(player)" class="btn btn-success btn-app" style="margin-top:5px;background-color:#00a65a;color:white;"><i class="fa fa-check"></i> All Paid!</button></div>
									</div>
								</div>
							</div>
							<div class="col-sm-7">
								<h4>Invite friends!</h4>
								<div class="row">
									<div class="alert alert-info col-xs-8" style="height:60px;font-size:1.2em;font-weight:bold;margin-left:25px;background-color: #F4F4F4 !important;">
								        <div class="info info-info" style="color:black">[[niceUrl]]</div>
							        </div>
							        <button class="btn-app" id="copyLink" data-clipboard-text="[[absUrl]]"><i class="fa fa-copy"></i> Copy</button>
							    </div>
							    <div class="row">
									<div class="col-lg-12 text-muted" style="margin-left:20px;position:relative;bottom:10px;">*Copy the link above and share it to your friends along with the game password</div>
								</div>
								<h4>Cost Per Square</h4>
								<form role="form">
					              <div class="box-body">
					                <div class="form-group">
					                  <label for="exampleInputEmail1">Change Square Cost</label>
					                  <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
					                </div>
								  </div>
					              <div class="box-footer">
					                <button type="submit" class="btn btn-primary pull-right">Submit</button>
					              </div>
					            </form>
								<hr>
								<h4>Reset Password</h4>
								<form role="form">
					              <div class="box-body">
					                <div class="form-group">
					                  <label for="exampleInputEmail1">New Password</label>
					                  <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
					                </div>
					                <div class="form-group">
					                  <label for="exampleInputPassword1">Confirm Passoword</label>
					                  <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
					                </div>
								  </div>
					              <div class="box-footer">
					                <button type="submit" class="btn btn-primary pull-right">Submit</button>
					              </div>
					            </form>
							</div>
				  		</div>
				  	</div>
				  	<div class="box-body" ng-show="pageLoading">
				  		<i class="fa fa-spinner fa-spin"></i>
				  	</div>
				</div>
			</div>
		</div>
	</section>
	<script type="text/ng-template" id="buy-square.html">
		<div class="modal-header">
		  <button type="button" ng-click="cancel()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		    <h3 class="modal-title">Claim Square: [[ squareName ]]</h3>
		</div>
		<div class="modal-body text-center">
		    Square Cost:<strong> [[squareCost | currency]]</strong><br>
		    Pay Game Admin:<br>
		    Once payment is marked recieved, the square will officially be yours.<br>
		</div>
		<div class="modal-footer">
		    <button class="btn btn-default" type="button" ng-click="cancel()">Cancel</button>
		    <button class="btn btn-primary" type="button" ng-click="ok()">Save</button>
		</div>
	</script>
	<script type="text/ng-template" id="mark-paid.html">
		<div class="modal-header">
		  <button type="button" ng-click="cancel()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		    <h3 class="modal-title">[[ selectedPlayer.user.email ]]</h3>
		</div>
		<div class="modal-body">
		   <div class="row">
			   <div class="col-md-12 text-center" style="font-size:1.2em">
			    Paid For
			   	<input class="form-control input-lg" style="width:80px;display:inline;text-center" ng-change="watchBalance()" ng-model="selectedPlayer.paidUp" type='number' value="0" min="0"  step="1"/> 
			   	Sqaures
			   </div>
			</div>
			<div class="row" style="font-size:1.2em;margin-top:15px;">
		   		<div class="col-xs-12 text-center" style="color:#00a65a">Amount: [[totalPaid | currency]]</div>
		   		<div class="col-xs-12 text-center" ng-show="owedBalance > 0" style="color:#d73925">Balance: [[owedBalance | currency]]
		   	</div>
		</div>
		<div class="modal-footer">
		    <button class="btn btn-default" type="button" ng-click="cancel()">Cancel</button>
		    <button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
		</div>
	</script>
</div>
	
@stop

@section('bottomscript')

@stop