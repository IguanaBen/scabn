angular.module('auth', ['ui.bootstrap','angularPayments']);
var Authorize = function ($scope, $modal, $log) {

  $scope.items = ['item1', 'item2', 'item3'];
  
  $scope.open = function (url) {
	$scope.url=url;
	

    var modalInstance = $modal.open({
      templateUrl: '../wp-content/plugins/simple-cart-buy-now/classes/authorize.html',
      controller: ModalInstanceCtrl,
      resolve: {
        items: function () {
          return $scope.items;
        },url: function () { return $scope.url}
      }
    });

    modalInstance.result.then(function (selectedItem) {
      $scope.selected = selectedItem;
    }, function () {
      $log.info('Modal dismissed at: ' + new Date());
    });
  };
};

var ModalInstanceCtrl = function ($scope, $modalInstance, $http,$log,items,url) {
	
	$scope.url=url;	
	$scope.form="action=cartcontents";	
	
	$scope.send = function(){
		$http({
			method:'POST',
			url:'http://dev.iguanaworks.net/wp-admin/admin-ajax.php',
			data:$scope.form,
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function (data){ $scope.data=data})
	};				

	$scope.send();
	

	$scope.charge = function(){
		
		//var url = 'https://developer.authorize.net/tools/paramdump/index.php';
		//var url ='https://iguanaworks.net/Scripts/Log.php';
		//var data.action='chargecart';		
		//var str=[];
		
		//for(var p in data){
		//	str.push(encodeURIComponent(p)+"="+encodeURIComponent(data[p]));
		//}
		//var newdata=str.join("&");			
		
		//$http.get(url+'?'+newdata);		
		
		var str="action=chargecart";
		var senddata = {};
		senddata['data']=$scope.data;
		senddata['action']='chargecart';
		$log.info("Should POST this data: " + senddata);			
		$http({
			method:'POST',
			url:$scope.url,	
			data: senddata,
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}					
			});		
							
	}


  $scope.items = items;
  $scope.selected = {
    item: $scope.items[0]
  };

  $scope.ok = function () {
    $modalInstance.close($scope.selected.item);
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
};