samyChanApp.controller('HeaderCtrl', function($scope, backendUrlGenerator, nav) {
    $scope.uploadUrl = backendUrlGenerator.buildUploadUrl();

    nav.loadItems(function(items) {
        $scope.navItems = items;
    });
});