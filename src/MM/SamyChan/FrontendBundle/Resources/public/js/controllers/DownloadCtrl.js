samyChanApp.controller('DownloadCtrl', function($scope, eventTracker, $timeout) {

    $scope.currentStep = 1;

    $scope.startDownloader = function() {
        $('#modal-download').foundation('reveal', 'open');
        eventTracker.track('Downloader', 'open');
    }

    $scope.download = function() {
        eventTracker.track('Downloader', 'download');
        $scope.currentStep = 2;
    }

    $scope.donate = function() {
        eventTracker.track('Downloader', 'donate');
    }

    $("#download-button").click($scope.startDownloader);
});