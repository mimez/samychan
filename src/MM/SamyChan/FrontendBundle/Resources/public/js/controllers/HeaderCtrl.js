samyChanApp.controller('HeaderCtrl', function($scope, backendUrlGenerator, nav) {
    $scope.uploadUrl = backendUrlGenerator.buildUploadUrl();
    $scope.downloadUrl = backendUrlGenerator.buildDownloadUrl();

    nav.loadItems(function(items) {
        $scope.navItems = items;
    });
});