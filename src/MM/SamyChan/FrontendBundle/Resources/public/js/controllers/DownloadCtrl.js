samyChanApp.controller('DownloadCtrl', function($scope, eventTracker) {

    $scope.startDownloader = function() {
        $('#modal-download').foundation('reveal', 'open');
        eventTracker.track('Downloader', 'open');
    }

    $scope.download = function() {
        eventTracker.track('Downloader', 'download');
    }

    $("#download-button").click($scope.startDownloader);
});