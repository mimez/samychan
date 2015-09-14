var samyChanApp = angular.module('SamyChanApp', ['ngRoute']);

// configure our routes
samyChanApp.config(function ($routeProvider) {
    $routeProvider

        // route for the about page
        .when('/files/:scmFileId', {
            templateUrl: '/bundles/mmsamychanfrontend/partials/scmfile.html',
            controller: 'ScmFileCtrl'
        });
});

samyChanApp.factory('backendUrlGenerator', function() {
    var scmPackageHash = "55ef0d92d444d";
    return {
        buildFileUrl: function(scmFileId) {
            return "/backend/" + scmPackageHash + "/file/" + scmFileId + "/json/";
        }
    }
});

samyChanApp.controller('ScmFileCtrl', function ($scope, $http, $routeParams, backendUrlGenerator) {
    $scope.scmFile = {}
    $scope.modifiedChannels = {};

    /**
     * get count of modified channels
     *
     * @returns {Number}
     */
    $scope.getModifiedChannelsCount = function() {
        return Object.keys($scope.modifiedChannels).length;
    }

    $http.get(backendUrlGenerator.buildFileUrl($routeParams.scmFileId)).then(function(res) {
        $scope.scmFile = res.data;
    });
});

samyChanApp.controller('ScmFavoriteCtrl', function ($scope) {

});