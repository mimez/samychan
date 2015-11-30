samyChanApp.controller('HeaderCtrl', function($scope, backendUrlGenerator, nav) {
    $scope.uploadUrl = backendUrlGenerator.buildUploadUrl();
    $scope.downloadUrl = backendUrlGenerator.buildDownloadUrl();

    $scope.download = function() {
        $('#modal-download').foundation('reveal', 'open');
    }

    nav.loadItems(function(items) {
        $scope.navItems = items;
    });
});