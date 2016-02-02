var app = angular.module('scoreSquares', ['ngRoute', 'ui.bootstrap']);  

app.service('SettingsService', function ($location, $http, $timeout, $routeParams, $window) {
  return new (function () {
  	this.saveMethod = 'POST';
    this.savePath = '';
    this.redirectPath = '';
    this.loading = false;

    this.save = function ($scope, callback) {
      $scope.saveAttempted = true;
      var redirect = this.getRedirectPath();

      if ($scope.form.$valid) {
        var self = this;
        $http({method: this.saveMethod, url: this.savePath, data: $scope.formData}).
        success(function(data, status, headers, config) {          
          if (data.redirect !== undefined) {
            $location.path(data.redirect);
          }else{
            $location.path(redirect);
          }          
        
          self.showAlert(data.flash, "success");

          if (callback) {
            callback(true);
          };
        }).
        error(function(data, status, headers, config) {
          $scope.formError = true;
          self.showAlert(data.flash, "error");
        });
      } else {
        $scope.formError = true;
      }
    };

    this.showAlert = function (message, type) {
      var delay = 2000; //Duration of the alert (In ms)

      $('#alert-box').removeClass("alert-success alert-error alert-info alert-warning");
      $('#alert-box').addClass("alert-" + type);
      $('#alert-box').html(message).fadeIn();

      if(type == "warning"){
        delay = 4000;
      }

      $timeout(function() { $('#alert-box').fadeOut(); }, delay);
    };

    this.showLoading = function () {
      this.loading = true;
      $('#ajaxLoader').show();
    };

    this.hideLoading = function () {
      this.loading = false;
      $('#ajaxLoader').fadeOut();
    };
  })();
});
app.config(['$interpolateProvider', function($interpolateProvider) {
  $interpolateProvider.startSymbol('[[');
  $interpolateProvider.endSymbol(']]');
}]);
