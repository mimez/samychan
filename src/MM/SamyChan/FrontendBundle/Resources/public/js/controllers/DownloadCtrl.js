samyChanApp.controller('DownloadCtrl', function($scope, eventTracker, $timeout) {

    $scope.startDownloader = function() {
        $('#modal-download').foundation('reveal', 'open');
        eventTracker.track('Downloader', 'open');
        $("#adcontent").html('<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1076262210850893" data-ad-slot="3923993579" data-ad-format="auto"></ins>');
        $(document).on('opened.fndtn.reveal', '[data-reveal]', function () {
            setTimeout(function() {
                (adsbygoogle = window.adsbygoogle || []).push({});
            }, 1);
        });
    }

    $scope.download = function() {
        eventTracker.track('Downloader', 'download');
    }

    $("#download-button").click($scope.startDownloader);
});