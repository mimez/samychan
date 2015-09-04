var app = angular.module('SamyChanApp', ['ngHandsontable']);
app.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});
app.controller('ChannelCtrl', function ($scope, $http) {
    $scope.channels = [];
    $scope.modifiedChannels = {};
    $scope.saveableColumns = ["channelNo", "name"];
    $scope.channelData = {};

    $scope.getModifiedChannelsCount = function() {
        return Object.keys($scope.modifiedChannels).length;
    }

    $scope.loadChannels = function() {
        $http.get($('#grid').attr('data-url')).then(function(res) {

            $.each(res.data.data, function(index, item) {
                $.each([1,2,3,4,5], function(x, no) {
                    if (typeof res.data.data[index]['fav' + no + 'sort'] == "undefined") {
                        return;
                    }
                    res.data.data[index]['fav' + no + 'sort'] = res.data.data[index]['fav' + no + 'sort'] != null && res.data.data[index]['fav' + no + 'sort'] >= 0;
                })
            });

            $scope.channelData = res.data.data;
            $scope.hotInstance.loadData($scope.channelData);

            $.unblockUI();
        });

    }

    $scope.renderFav = function() {
        return true;
    }

    $scope.filterChannel = function(event) {
        var searchTerm = $(event.target).val().toLowerCase();
        var newData = $.grep($scope.channelData, function(item, index) {
            if (item["name"] == null) {
                return false;
            }
            return (
                item["name"].toString().toLowerCase().indexOf(searchTerm) >= 0 ||
                item["channelNo"].toString() == searchTerm
            );
        });
        $scope.hotInstance.loadData(newData);
    }

    $scope.reorderChannels = function() {
        console.log($scope);
        $.blockUI();
        $.get($('#grid').attr('reorder-url'), $scope.loadChannels);
    }

    $scope.afterChange = function(changes, source) {

        if (changes == null || typeof changes.length == 'undefined') {
            return;
        }

        $.each(changes, function(index, change) {
            if (change[2] == change[3]) {
                return; // nothing has been changed
            }

            // row-index ermitteln
            var rowIndex = change[0];

            // channel-id anhand des row-index holen
            var channelId = $scope.hotInstance.getDataAtRowProp(rowIndex, "channelId");

            // hole neue daten des channels
            var channelData = {};
            $.each($scope.saveableColumns, function(index, columnName) {
                channelData[columnName] = $scope.hotInstance.getDataAtRowProp(rowIndex, columnName);
            });

            $scope.modifiedChannels[channelId] = channelData;
            $scope.$apply();
        });

        this.sort(0, true);
    }

    $scope.afterInit = function() {
        $scope.hotInstance = this;
    }

    $scope.saveChannels = function() {
        $.blockUI();
        // submit data to server
        $.post($('#grid').attr('data-url'), {'channels': $scope.modifiedChannels}).
            success(function(data, status, headers, config) {
                $scope.modifiedChannels = {};
                $.unblockUI();
                $scope.$apply();
            }).
            error(function(data, status, headers, config){
                $.unblockUI();
            }
        );
    }


    $scope.loadChannels();
});
resizer = function() {
    if ($(".to-bottom").length == 0) {
        return;
    }
    $(".to-bottom").css("height", ($(window).height() - $(".to-bottom").position().top - 20) + "px");
};
$(document).ready(resizer)
$(window).bind('resize', resizer);
