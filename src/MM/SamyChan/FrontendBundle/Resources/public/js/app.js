var samyChanApp = angular.module('SamyChanApp', ['ngRoute', 'ngHandsontable']);

// configure our routes
samyChanApp.config(function ($routeProvider) {
    $routeProvider

        // route for the about page
        .when('/files/:scmFileId', {
            templateUrl: '/bundles/mmsamychanfrontend/partials/scmfile.html',
            controller: 'ScmFileCtrl'
        })
        .when('/favorites/:favNo', {
            templateUrl: '/bundles/mmsamychanfrontend/partials/favorite.html',
            controller: 'ScmFavoriteCtrl'
        })
        .otherwise({
            redirectTo: '/files/' + $("body").data("first-scm-file-id")
        });
});
samyChanApp.run(function($http, $rootScope, backendUrlGenerator) {
    $http.get(backendUrlGenerator.buildPackageUrl()).then(function(res) {
        $rootScope.scmFiles = res.data.files;
        $rootScope.favorites = res.data.favorites;
    });
});

samyChanApp.controller('TestCtrl', function($scope, $http, $routeParams, backendUrlGenerator) {


});


resizer = function() {
    if ($(".to-bottom").length == 0) {
        return;
    }
    $(".to-bottom").css("height", ($(window).height() - $(".to-bottom").position().top - 20) + "px");
};
$(document).ready(resizer)
$(window).bind('resize', resizer);