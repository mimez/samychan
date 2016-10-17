samyChanApp.controller('DownloadCtrl', function($scope, eventTracker, backendUrlGenerator) {

    $scope.downloadUrl = backendUrlGenerator.buildDownloadUrl();

    $scope.televisions = [
        {
            "label": "KS9090",
            "modelName": "Samsung KS7590 SUHD/4K LED TV, Curved",
            "link": "http://amzn.to/2efdeVT",
            "image": "//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B01EJXOKHW&Format=_SL160_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=bastelnnet-21",
            "refreshrate": "100 Hz",
            "uhd": "4K Ultra-HD",
            "features": "HDR 1000, QuantumDot Color, 10 Bit Farbwiedergabe",
            "amazonLinks": {
                "49\"": "http://amzn.to/2eaoaWr",
                "55\"": "http://amzn.to/2earukz",
                "65\"": "http://amzn.to/2eapk4e"
            }
        },
        {
            "label": "KS8090",
            "modelName": "Samsung KS7590 SUHD/4K LED TV, Flat",
            "link": "http://amzn.to/2eyHXSh",
            "image": "//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B01DOO487E&Format=_SL160_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=bastelnnet-21",
            "refreshrate": "100 Hz",
            "uhd": "4K Ultra-HD",
            "features": "HDR 1000, QuantumDot Color, 10 Bit Farbwiedergabe",
            "amazonLinks": {
                "49\"": "http://amzn.to/2eyP8cY",
                "55\"": "http://amzn.to/2eaqz3x",
                "65\"": "http://amzn.to/2e1YNte",
                "78\"": "http://amzn.to/2ebKpfZ"
            }
        },
        {
            "label": "KS7590",
            "modelName": "Samsung KS7590 SUHD/4K LED TV, Curved",
            "link": "http://amzn.to/2eyIljr",
            "image": "//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B01DVYWZOA&Format=_SL160_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=bastelnnet-21",
            "refreshrate": "100 Hz",
            "uhd": "4K Ultra-HD",
            "features": "HDR 1000, QuantumDot Color, 10 Bit Farbwiedergabe",
            "amazonLinks": {
                "49\"": "http://amzn.to/2ebLcgV",
                "55\"": "http://amzn.to/2ebLrZw",
                "65\"": "http://amzn.to/2eyQggR"
            }
        },
        {
            "label": "KS7090",
            "modelName": "Samsung KS7090 SUHD/4K LED TV, Flat",
            "link": "http://amzn.to/2ebKUGY",
            "image": "//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B01FE7KC9W&Format=_SL160_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=bastelnnet-21",
            "refreshrate": "100 Hz",
            "uhd": "4K Ultra-HD",
            "features": "HDR 1000, QuantumDot Color, 10 Bit Farbwiedergabe",
            "amazonLinks": {
                "49\"": "http://amzn.to/2ebM98P",
                "55\"": "http://amzn.to/2e23Rxv",
                "60\"": "http://amzn.to/2diLMde",
                "65\"": "http://amzn.to/2e21xqy"
            }
        },
        {
            "label": "KU6509",
            "modelName": "Samsung KU6509 UHD/4K LED TV, Curved",
            "link": "http://amzn.to/2eauNbu",
            "image": "//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B01CJS8OPS&Format=_SL160_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=bastelnnet-21",
            "refreshrate": "50 Hz",
            "uhd": "4K Ultra-HD",
            "features": "Active Crystal Colour, HDR, UHD",
            "amazonLinks": {
                "43\"": "http://amzn.to/2eatEAF",
                "49\"": "http://amzn.to/2e221wF",
                "55\"": "http://amzn.to/2eatbyc",
                "60\"": "http://amzn.to/2e21YRE",
                "65\"": "http://amzn.to/2easZza"
            }
        },
        {
            "label": "KU6409",
            "modelName": "Samsung KU6409 UHD/4K LED TV, Flat",
            "link": "http://amzn.to/2eavrpt",
            "image": "//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B01CJS8J3A&Format=_SL160_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=bastelnnet-21",
            "refreshrate": "50 Hz",
            "uhd": "4K Ultra-HD",
            "features": "Active Crystal Colour, HDR, UHD",
            "amazonLinks": {
                "43\"": "http://amzn.to/2eatqcv",
                "43\"": "http://amzn.to/2e25fAo",
                "49\"": "http://amzn.to/2e24O9a",
                "55\"": "http://amzn.to/2eavrpt",
                "65\"": "http://amzn.to/2e226Au"
            }
        }
    ];

    $scope.startDownloader = function() {
        alert('foo');
        reset();
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

    $scope.trackAmazon = function() {
        eventTracker.track('Downloader', 'clickAmazon');
    }
});