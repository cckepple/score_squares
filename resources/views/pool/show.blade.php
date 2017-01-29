@extends('layouts.master')
@section('topscript')
<style type="text/css">
	.disabled {
		opacity: .65;
	}
	.atl-text{
		display: block;
    	font-weight: bold;
    	font-size: 21px;
    	padding-left: 10%;
	}

	.pats-div{
		padding-left: 18%;
    	margin-bottom: 17px;
	}

	.team-logos {
		max-height: 100px;
		max-width: 120px;
	}

	.pats-text{
		font-size:1.5em;
		font-weight:bold;
	}
	.car-image{
		max-width:100px;
		max-height:100px;
		position:relative;
		bottom:40px;
	}

	.square-table {
		position: relative;
		bottom:60px;
	}
	@media only screen and (max-width: 1000px) {
		.content-containers{
			overflow-x:auto;
		}
	}
	@media only screen and (max-width: 940px) {
		.team-logos {
			max-height: 75px;
			max-width: 75px;
		}
	    .atl-text{
	    	font-size:1em;
	    }
	    .pats-text{
			font-size:1em;
	    }
	}

	@media only screen and (max-width: 767px) {
		.pats-div {
			padding-left: 25%;
		}

		.team-logos {
			max-height: 50px;
			max-width: 50px;
		}
	    .copyBtn{
	    	display: none;
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
			$scope.nflGameInfo = data.nflGameInfo;
			$scope.winners = data.winners;
			console.log($scope.winners);
			$scope.admin = data.admin;
			$scope.grid = []
			$scope.homeScores = data.homeScores;
			$scope.awayScores = data.awayScores;
			var i = 0;
			for (var r = 0; r < 10; r++) {
				$scope.grid.push({'row':$scope.awayScores[r], 'slots':[]});
				var currentRow = $scope.grid[r];
				for (var c = 10; c > 0; c--) {
					squares[i].active = false;
					if (squares[i].user_id == $scope.myId) {
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
		});
	}

	$scope.selectSlot = function(slot) 
	{
		slot.active = true;
		$scope.claimSquareModal(slot);
	}

	$scope.claimSquareModal = function (square) 
	{

		var modalInstance = $uibModal.open({
		  animation: true,
		  templateUrl: 'buy-square.html',
		  controller: 'ClaimedModalInstanceCtrl',
		  size: 'md',
		  resolve: {
	        square: function () {
	          return square;
	        },
	        squareCost: function() {
	        	return $scope.gameInfo.square_cost;
	        },
	        admin: function() {
	        	return $scope.admin;
	        }
	      }
		});

		modalInstance.result.then(function (squareName, squareCost) {
		  $scope.makingPurchase = true;
		  $scope.claimSquare(square);
		}, function () {
		  square.active = false;
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

	$scope.removePayment = function (player) 
	{

		var modalInstance = $uibModal.open({
		  animation: true,
		  templateUrl: 'remove-pay.html',
		  controller: 'RemovePayModalInstanceCtrl',
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
			$scope.removePlayerPay(selectedPlayer);
		}, function () {
		  console.log('Modal dismissed at: ' + new Date());
	  	});
	};

	$scope.unClaimSqaure = function(player)
	{

	  	var modalInstance = $uibModal.open({
		  animation: true,
		  templateUrl: 'unclaim-square.html',
		  controller: 'UnclaimedModalInstanceCtrl',
		  size: 'md',
		  resolve: {
	        player: function() {
	        	return player;
	        },
	      }
		});

		modalInstance.result.then(function (selectedPlayer) {
			$scope.removePlayerClaim(selectedPlayer);
		}, function () {
		  console.log('Modal dismissed at: ' + new Date());
	  	});
	}

	$scope.claimSquare = function(square)
	{
		$http.get('/api/square/'+square.id+'/purchase').success(function(data){
			$scope.getData();
		}).error(function(data) {
			console.log(data);
		});
	}

	$scope.playerPay = function(player)
	{
		var poolPlayer = {'id':player.id,'poolId':player.pool_id,paidUp:player.paidUp};
		$http.post('/api/pool/player-paid', {poolPlayer: poolPlayer}).success(function(data){
			$scope.getData();
		}).error(function(data){
			console.log(data);
		});
	}

	$scope.removePlayerPay = function(player)
	{
		var poolPlayer = {'id':player.id,'poolId':player.pool_id,paidDown:player.paidDown};
		$http.post('/api/pool/remove-player-pay', {poolPlayer: poolPlayer}).success(function(data){
			console.log(data);
			$scope.getData();
		}).error(function(data){
			console.log(data);
		});
	}

	$scope.removePlayerClaim = function(player)
	{
		var poolPlayer = {'id':player.id, 'poolId':player.pool_id,claimDown:player.claimDown};
		$http.post('/api/pool/remove-player-claim', {poolPlayer: poolPlayer}).success(function(data){
			$scope.getData();
		}).error(function(data){
			console.log(data);
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
	$scope.getData = function()
	{
		$scope.getPoolSquares();
		$scope.getPoolPlayers();
	}

	$scope.copyAnimation = function()
	{
		var copyTxt = jQuery('.copy-anim').text();
		if(copyTxt.indexOf('Click to copy') > -1){
			var $copy = jQuery('.copy-anim');
			$copy.hide();
			$copy.text('Copied!');
			$copy.fadeIn();
			setTimeout(function(){
				$copy.hide();
				$copy.text('Click to copy, share with friends!');
				$copy.fadeIn();
			},2000);
		}
	}

	$scope.setGridScores = function()
	{
		$http.get('/api/pool/'+$scope.poolId+'/set-scores')
		.success(function(data){
			if(data.indexOf('squares available') > -1){
				var $alertEm = jQuery('#setScoreInfo');
				if($alertEm.text().length === 0){
					$alertEm.text('Cannot set scores until all squares are owned.');
					$alertEm.addClass('alert-danger');
					$alertEm.fadeIn();
					setTimeout(function(){
						$alertEm.fadeOut();
						$alertEm.text('');
						$alertEm.removeClass('alert-danger');
					}, 3000);	
				}
			}else{
				$window.location.reload();
			}
		})
		.error(function(data){
			console.log(data);
		});
	}
	var urlArr1 = $location.absUrl().split('/');
	var urlArr2 = $location.absUrl().split('//');
	$scope.absUrl = $location.absUrl();
	$scope.poolId = urlArr1[urlArr1.length - 1];
	$scope.niceUrl = urlArr2[1];

	$scope.letters = ['A','B','C','D','E','F','G','H','I','J'];
	$scope.divs  = [{'name':'squares','id':1,'active':true},{'name':'players','id':2,'active':false},{'name':'board','id':3,'active':false},{'name':'admind','id':4,'active':false}]
	
	$scope.makingPurchase = false;
	$scope.pageLoading = true;

	$scope.getData();
	

});

app.controller('UnclaimedModalInstanceCtrl', function ($scope, $uibModalInstance, player) {
  $scope.selectedPlayer = player;
  $scope.selectedPlayer.claimDown = 0;
  $scope.ok = function () {
    $uibModalInstance.close($scope.selectedPlayer);
  };

  $scope.cancel = function () {
  	$scope.selectedPlayer.claimDown = 0;
    $uibModalInstance.dismiss('cancel');
  };

  $scope.watchBalance = function() {
  	if($scope.selectedPlayer.claimDown > player.oweSquareCount){
		$scope.selectedPlayer.claimDown = player.oweSquareCount;
	}
  }
});

app.controller('ClaimedModalInstanceCtrl', function ($scope, $uibModalInstance, square, squareCost, admin) {
  $scope.square = square;
  $scope.squareCost = squareCost;
  $scope.squareName = String.fromCharCode(64 + parseInt(square.column))+'-'+square.row;
  $scope.admin = admin;

  $scope.ok = function () {
    $uibModalInstance.close($scope.squareName, $scope.squareCost);
  };

  $scope.cancel = function () {
    $uibModalInstance.dismiss('cancel');
  };
});

app.controller('PaidModalInstanceCtrl', function ($scope, $uibModalInstance, player, squareCost) {
	$scope.selectedPlayer = player;
	$scope.selectedPlayer.paidUp = 0;
	$scope.totalPaid = 0;
	$scope.squaresPaid = 0;
	$scope.owedBalance = (player.oweSquareCount  * squareCost);

	$scope.ok = function () {
	  $uibModalInstance.close($scope.selectedPlayer, $scope.squaresPaid, $scope.holdClaim);
	};

	$scope.cancel = function () {
	  $scope.selectedPlayer.paidUp = 0;
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

app.controller('RemovePayModalInstanceCtrl', function ($scope, $uibModalInstance, player, squareCost) {
	$scope.selectedPlayer = player;
	$scope.selectedPlayer.paidDown = 0;
	$scope.totalPaid = 0;
	$scope.squaresPaid = 0;
	$scope.owedBalance = (player.oweSquareCount  * squareCost);

	$scope.ok = function () {
	  $uibModalInstance.close($scope.selectedPlayer, $scope.holdClaim);
	};

	$scope.cancel = function () {
	  $scope.selectedPlayer.paidDown = 0;
	  $uibModalInstance.dismiss('cancel');
	};

	$scope.watchBalance = function()
	{
		if ($scope.selectedPlayer.paidDown > 0){
			if ($scope.selectedPlayer.paidDown > $scope.selectedPlayer.paidSquareCount) {
				$scope.selectedPlayer.paidDown = $scope.selectedPlayer.paidSquareCount;
			};
			$scope.owedBalance = $scope.selectedPlayer.paidDown * squareCost;
		};

	}
});



</script>
@stop

@section('content')
<div ng-app="scoreSquares" ng-controller="ShowPoolCtrl">
	<section class="content-header ng-cloak">
	  	<h4>SUPER BOWL 51 - [[gameInfo.name]]</h4>
	</section>
	<section class="content">
	  	<div class="row">
	  		<div class="col-lg-12">
				<div class="box box-default">
					<div class="box-body ng-cloak content-containers"  ng-hide="pageLoading">
					  	<div class="row">
					  		<div class="col-sm-12">
						  		<div class="btn-group text-center" role="group">
									<button class="btn btn-primary" ng-class="{'active': divs[0].active}" ng-click="showDiv(1)">Squares</button>
									<button class="btn btn-primary" ng-class="{'active': divs[1].active}" ng-click="showDiv(2)">Scores</button>
								</div>	
						  	</div>
					  	</div>
					  	<hr>
					  	<div ng-show="divs[0].active">
					  		<div class="row">
					  			<div class="pats-div">
					  			<img src="/img/team_logos/pats.png" class="team-logos">
						  		<span class="pats-text">Patriots</span>
						  		</div>
					  		</div>
						  	<div class="row">
						  		<div class="col-xs-1" style="padding-top:75px;">
						  			<img src="/img/team_logos/falcons.png" class="team-logos">
						  			<span class="atl-text">Falcons</span>
						  		</div>
						  		<div class="col-xs-9">
								    <table class="table" style="margin-left:35px;">
								    	<tr style="border-left:15px solid black;border-right:1px solid #f4f4f4;">
								    		<td style="border-top:15px solid black;"></td>
								    		<td ng-show="gameInfo.status == 1" ng-repeat="letter in letters" style="border-top:15px solid #0D254C;"></td>
								    		<td ng-show="gameInfo.status == 2" ng-repeat="score in homeScores" style="border-top:15px solid #0D254C;"><strong>[[score]]</strong></td>
								    	</tr;>
								    	<tr ng-repeat="row in grid">
								    		<td style="border-left:15px solid #A6192D; width:1px;"><span ng-show="gameInfo.status == 2"><strong>[[row.row]]</strong></span></td>
								    		<td ng-click="selectSlot(slot)" ng-class="{'info':slot.active,'bg-gray':slot.status.id == 2,'bg-green':slot.mySquare && slot.status.id ==3, 'bg-gray disabled':slot.status.id == 3,'bg-white':slot.status.id == 4}" ng-repeat="slot in row.slots" class="text-center" style="height:80px;width:80px;border:grey solid 1px;cursor:pointer;">
								    			<i ng-show="makingPurchase && slot.active" class="fa fa-circle-o-notch fa-spin"></i>
								    			<div ng-hide="makingPurchase && slot.active">
								    				<i class="fa fa-star text-center fa-spin" ng-show="slot.status.id == 4" style="font-size:.8em;color:#ffdd54;"></i>
								    				<span >[[slot.status.name]]</span>
								    				<i class="fa fa-star text-center fa-spin" ng-show="slot.status.id == 4" style="font-size:.8em;color:#ffdd54;"></i>
								    				<br><br><span style="" ng-show="slot.user.name">[[slot.user.name]]</span>
								    				<br>
								    				<span style="font-size:1.1em;padding:2px;" ng-show="gameInfo.status > 1">
								    					<span style="font-weight:bold;color:#A6192D;">[[slot.away_score]]</span>
								    					 - 
								    					<span style="font-weight:bold;color: #0D254C;">[[slot.home_score]]</span>
								    				</span>
								    				<!-- <div ng-show="slot.status.id == 4" style="color:#5AC594"><strong>$125.00!</strong></div> -->
								    			</div>
								    		</td>
								    	</tr>
								    </table>
								</div>
							</div>
						</div>
						<div ng-show="divs[1].active" >
							<div class="row">
								<div class="col-sm-6">
									<h4>Score Board</h4>
									<table class="table">
										<tbody class="text-center">
											<tr>
												<td></td>
												<td><img src="/img/team_logos/falcons.png" style="max-height:78px;"></td>
												<td><img src="/img/team_logos/pats.png" style="max-width:100px;"></td>	
												<td style="padding-top: 88px;">Winner!</td>
											</tr>
											<tr>
												<td>Quarter 1</td>
												<td>[[nflGameInfo.fq_away_score]]</td>
												<td>[[nflGameInfo.fq_home_score]]</td>
												<td>[[winners[1].name]]</td>
											</tr>
											<tr>
												<td>Quarter 2</td>
												<td>[[nflGameInfo.sq_away_score]]</td>
												<td>[[nflGameInfo.sq_home_score]]</td>
												<td>[[winners[2].name]]</td>
											</tr>
											<tr>
												<td>Quarter 3</td>
												<td>[[nflGameInfo.tq_away_score]]</td>
												<td>[[nflGameInfo.tq_home_score]]</td>
												<td>[[winners[3].name]]</td>
											</tr>
											<tr>
												<td>Quarter 4</td>
												<td>[[nflGameInfo.lq_away_score]]</td>
												<td>[[nflGameInfo.lq_home_score]]</td>
												<td>[[winners[4].name]]</td>
											</tr>

										</tbody>
									</table>
								</div>
								<div class="col-sm-6">
									<h4>Invite friends!</h4>
									<div class="col-xs-12">
										<div class="alert alert-info" style="height:60px;font-size:1.15em;font-weight:bold;background-color: #F4F4F4 !important;cursor: pointer;" id="copyLink" data-clipboard-text="[[absUrl]]" ng-click="copyAnimation()">
									        <div class="info info-info" style="color:black;">
									        	<span>[[niceUrl]]</span>
									        	<span class="pull-right copy-anim" style="padding-right:25px;">Click to copy, share with friends!</span>
									        </div>
								        </div>
							        </div>
									<br>
									<h4>How to Play</h4>
									<div class="col-xs-12">
							        	<div class="callout callout-info">
						                	<ol style="font-size:1.1em;">
						                		<li>It's a lottery, so while squares are open simply claim one or more!</li>
						                		<li>Once all squares are claimed, the numbers 0-9 are randomly assigned along the grid for both teams.</li>
						                		<li>While watching the game, at the end of each quarter check the grid using the last digit of each teams score</li>
						                			<ul>
						                				<li>Example: If the patriots are up 27-3 at the end of quarter one, the person holding square 3-7 on the grid wins!</li>
						                			</ul>
						                	</ol>
						              	</div>
						            </div>
								</div>
							</div>
							<div class="row" ng-show="myId == admin.user.id">
									<div class="col-sm-8 col-sm-offset-2" style="margin-bottom:15px;">
									<br>
									<hr>
									<h3 class="text-center">Admin</h3>
									<div class="text-center">
										<button class="btn btn-info" ng-click="setGridScores()" ng-show="gameInfo.status === 1">Set Grid Scores</button>
										<div id="setScoreInfo" style="display: none;margin-top:5px;"></div>
									</div>
									<hr>
									<h4>Current Players</h4>
									<div class="row" ng-repeat="player in players">
										<div class="col-xs-12">
								          <div class="info-box">
								            <span class="info-box-icon" ng-class="{'bg-green':player.totalSquareCount>0&&(player.paidSquareCount==player.totalSquareCount),'bg-orange':player.oweSquareCount>0&&player.paidSquareCount>0, 'bg-red':player.paidSquareCount==0&&player.totalSquareCount>0}">
								            	<i class="fa fa-question" ng-show="player.totalSquareCount==0"></i>
								            	<i class="fa fa-thumbs-o-up" ng-show="player.oweSquareCount==0&&player.totalSquareCount>0"></i>
								            	<i class="fa fa-money" ng-show="player.oweSquareCount>0"></i>
								            </span>
								            <div class="info-box-content" style="padding-top:0px;">
									            <span class="info-box-text">
									            	<span style="color:#00a65a" ng-show="player.totalSquareCount>0">
									            		[[player.paidSquareCount * gameInfo.square_cost | currency]]
									            		<span style="color:#d73925" ng-show="player.oweSquareCount>0">
															([[player.oweSquareCount * gameInfo.square_cost | currency]])
														</span>
													</span>
									            	[[player.user.email]]
												</span>
									            <div class="info-box-number" style="font-weight:normal;font-size:1em;">
									            	<span class="col-xs-12">
									            		Own:[[player.paidSquareCount]]
									            		<span class="btn btn-danger btn-xs pull-right" style="cursor:pointer;" ng-click="removePayment(player)" ng-show="player.paidSquareCount>0">Remove Payment</span>
									            	</span>
									            	<span class="col-xs-12">
									            		Pending: [[player.oweSquareCount]]
									            		<span class="btn btn-success btn-xs pull-right" style="cursor:pointer;" ng-click="markPaid(player)" ng-hide="player.totalSquareCount == player.paidSquareCount">Add Payment</span>
									            	</span>
									            	<span class="col-xs-12">
									            		Claimed: [[player.totalSquareCount]]
									            		<span class="btn btn-primary btn-xs pull-right" style="cursor:pointer" ng-click="unClaimSqaure(player)" ng-show="(player.totalSquareCount-player.paidSquareCount)>0">Unclaim Squares</span>
									            	</span>
												</div>
											  </span>
								            </div>
								          </div>
								        </div>
									</div>
								</div>
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
			<div ng-show="square.status.id == 1">
			    Square Cost:<strong> [[squareCost | currency]]</strong><br>
			    Pay Game Admin: [[admin.user.name]] ([[admin.user.email]])<br>
			    <span class="text-muted">Once payment is marked recieved, the square will officially be yours.</span<br>
			</div>
			<div ng-show="square.status.id == 2">
				This Square is currently pending payment.
			</div>
			<div ng-show="square.status.id == 3">
				This square is already claimed.
			</div>
		</div>
		<div class="modal-footer">
		    <button class="btn btn-default" type="button" ng-click="cancel()">Cancel</button>
		    <button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
		</div>
	</script>
	<script type="text/ng-template" id="mark-paid.html">
		<div class="modal-header">
		  <button type="button" ng-click="cancel()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		    <h3 class="modal-title">Add Payment</h3>
		</div>
		<div class="modal-body">
		   <div class="row">
		   		<div class="col-md-12 text-center" style="font-size:1.2em;margin-bottom:5px"><strong>[[selectedPlayer.user.email]]</strong></div>
			   	<div class="col-md-12 text-center" style="font-size:1.2em;margin-bottom:5px">
			    	Paid For
			   		<input class="form-control input-lg text-center" style="width:80px;display:inline;" ng-change="watchBalance()" ng-model="selectedPlayer.paidUp" type='number' value="0" min="0"  step="1"/> 
			   		Sqaures
			   	</div>
		   		<div class="col-xs-12 text-center" style="color:#00a65a;margin-bottom:5px">Amount: [[totalPaid | currency]]</div>
		   		<div class="col-xs-12 text-center" ng-show="owedBalance > 0" style="color:#d73925;margin-bottom:5px">Balance: [[owedBalance | currency]]
		   	</div>
		</div>
		<div class="modal-footer">
		    <button class="btn btn-default" type="button" ng-click="cancel()">Cancel</button>
		    <button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
		</div>
	</script>
	<script type="text/ng-template" id="remove-pay.html">
		<div class="modal-header">
		  <button type="button" ng-click="cancel()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		    <h3 class="modal-title">Remove Payment</h3>
		</div>
		<div class="modal-body">
		   <div class="row">
		   		<div class="col-md-12 text-center" style="font-size:1.2em;margin-bottom:5px"><strong>[[selectedPlayer.user.email]]</strong></div>
			   	<div class="col-md-12 text-center" style="font-size:1.2em">
			    	Remove Payment For
			   		<input class="form-control input-lg" style="width:80px;display:inline;text-center" ng-change="watchBalance()" ng-model="selectedPlayer.paidDown" type='number' value="0" min="0"  step="1"/> 
			   		Sqaures
			   	</div>
		   		<div class="col-xs-12 text-center" ng-show="owedBalance > 0" style="color:#d73925;margin-top:5px;">Balance: [[owedBalance | currency]]</div>
		   	</div>
		</div>
		<div class="modal-footer">
		    <button class="btn btn-default" type="button" ng-click="cancel()">Cancel</button>
		    <button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
		</div>
	</script>
	<script type="text/ng-template" id="unclaim-square.html">
		<div class="modal-header">
		  <button type="button" ng-click="cancel()" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		    <h3 class="modal-title">Remove Claim</h3>
		</div>
		<div class="modal-body">
		   <div class="row">
		   		<div class="col-md-12 text-center" style="font-size:1.2em;margin-bottom:5px">Remove</div>
		   		<div class="col-md-12 text-center" style="font-size:1.2em;margin-bottom:5px"><strong>[[selectedPlayer.user.email]]</strong></div>
			   	<div class="col-md-12 text-center" style="font-size:1.2em">
			    	Claim to
			   		<input class="form-control input-lg" style="width:80px;display:inline;text-center" ng-change="watchBalance()" ng-model="selectedPlayer.claimDown" type='number' value="0" min="0"  step="1"/> 
			   		Sqaures
			   	</div>
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