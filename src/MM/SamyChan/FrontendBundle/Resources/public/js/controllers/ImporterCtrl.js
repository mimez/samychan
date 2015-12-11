samyChanApp.controller('ImporterCtrl', function($scope, $rootScope, $http, backendUrlGenerator) {

    $scope.currentStep = null;
    $scope.importPackage = null;
    $scope.fileActions = {};
    $scope.changelog = {};
    $scope.startImporter = function() {
        $scope.scmFiles = $rootScope.scmFiles;
        reset();
        $('#modal-importer').foundation('reveal', 'open');
    }

    var reset = function() {
        $scope.currentStep = 1;
        $scope.$apply();
    }

    var uploadFile = function() {

        var fd = new FormData();
        fd.append('form[file]', $("#file").get(0).files[0]);
        fd.append('form[series]', 'auto');

        $.blockUI();
        $http.post(backendUrlGenerator.buildUploadJsonUrl(), fd, {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        }).success(uploadFileSuccess).error(function(data){
            $.unblockUI();
            showError(data);
        });
    }

    var uploadFileSuccess = function(data) {
        $http.get("/backend/" + data.scmPackage.hash + ".json")
            .success(function(data) {
                $scope.importPackage = data;
                $scope.currentStep++;
                $.unblockUI();
            })
            .error(function() {
                $.unblockUI();
                alert('error');
            })
        ;
    }

    var run = function(live) {
        var url = backendUrlGenerator.buildImportSettingsUrl();
        var data = {"files": $scope.fileActions, "dryrun": !live};

        // check if we have action to perform
        if (Object.keys(data.files).length == 0) {
            alert('Please specify at least one channel list for import');
            return;
        }

        $.blockUI();
        $.post(url, data)
            .success(function(res) {
                $scope.changelog = res.changes;
                $scope.currentStep++;
                $scope.$apply();
                $.unblockUI();
            })
            .error(function(res){
                $.unblockUI();
                alert('error');
            })
        ;
    }

    $scope.next = function() {
        if ($scope.currentStep == 1) {
            uploadFile();
        } else if ($scope.currentStep == 2) {
            run(false);
        } else if ($scope.currentStep == 3) {
            run(true);
            $rootScope.$broadcast("settingsImported");
        }
    }

    $scope.prev = function() {
        $scope.currentStep--;
    }

    var showError = function(data) {
        if (typeof data.messages != undefined) {
            alert(data.messages.join("\n"));
        } else {
            alert("error occured");
        }
    }

    $("#importer-button").click($scope.startImporter);
});