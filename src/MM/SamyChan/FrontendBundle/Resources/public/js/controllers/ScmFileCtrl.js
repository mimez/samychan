samyChanApp.controller('ScmFileCtrl', function ($scope, $http, $routeParams, backendUrlGenerator, nav) {

    $(document).resize();

    // declare vars
    $scope.scmFile = {}
    $scope.channels = [];
    $scope.modifiedChannels = {};
    $scope.saveableColumns = ["channelNo", "name"];
    $scope.channelData = {};
    $scope.searchTerm = null;
    $scope.hotInstance = null;

    var init = function() {
        nav.setItemActive('scmFile', $routeParams.scmFileId);
        $scope.loadChannels();
    }

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


    /**
     * Load Channels
     */
    $scope.loadChannels = function() {
        var url = backendUrlGenerator.buildFileUrl($routeParams.scmFileId);

        $http.get(url).then(function(res) {

            $.each(res.data.channels, function(index, item) {
                $.each([1,2,3,4,5], function(x, no) {
                    if (typeof res.data.channels[index]['fav' + no + 'sort'] == "undefined") {
                        return;
                    }
                    res.data.channels[index]['fav' + no + 'sort'] = res.data.channels[index]['fav' + no + 'sort'] != null && res.data.channels[index]['fav' + no + 'sort'] >= 0;
                })
            });

            $scope.channelData = res.data.channels;

            var columns = [];
            var colHeaders = [];
            for (columnName in res.data.fields) {

                // add column
                columns.push({
                    data: columnName,
                    type: typeof res.data.fields[columnName]["rendering"]["type"] != undefined ? res.data.fields[columnName]["rendering"]["type"] : "text",
                    readOnly: typeof res.data.fields[columnName]["editable"] != undefined ? !res.data.fields[columnName]["editable"] : true
                });

                // add to header
                colHeaders.push(res.data.fields[columnName]["label"]);
            }

            var container = document.getElementById('channel-list');
            $scope.hotInstance = new Handsontable(container, {
                columns: columns,
                colHeaders: colHeaders,
                stretchH: 'all',
                afterChange: $scope.afterChange,
                columnSorting: {
                    column: 0,
                    sortOrder: true
                },
                data: $scope.channelData
            });

            $.unblockUI();
        });

    }

    /**
     * Handle the change-event of a cell
     *
     * @param changes
     * @param source
     */
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

    /**
     * Filter channels
     */
    $scope.filterChannel = function() {
        var newData = $.grep($scope.channelData, function(item, index) {

            // if we have no search term, we show all channels
            if ($scope.searchTerm == null || $scope.searchTerm.length == 0) {
                return true;
            }

            if (item["name"] == null) {
                return false;
            }

            return (
                item["name"].toString().toLowerCase().indexOf($scope.searchTerm.toLowerCase()) >= 0 ||
                item["channelNo"].toString() == $scope.searchTerm.toLowerCase()
            );
        });

        $scope.hotInstance.loadData(newData);
    }

    /**
     * Save modifications
     */
    $scope.saveChannels = function() {
        $.blockUI();

        var url = backendUrlGenerator.buildFileUrl($routeParams.scmFileId);

        // submit data to server
        $.post(url, {'channels': $scope.modifiedChannels}).
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

    /**
     * Reorder Channels
     */
    $scope.reorderChannels = function() {

        // if we have modified channels, we have to save the channels before
        if ($scope.getModifiedChannelsCount() > 0) {
            alert($scope.getModifiedChannelsCount() + ' unsaved channel(s). Please save before reordering.');
            return;
        }

        var url = backendUrlGenerator.buildReorderUrl($routeParams.scmFileId);

        $.blockUI();
        $.get(url, $scope.loadChannels);
    }

    init();
});