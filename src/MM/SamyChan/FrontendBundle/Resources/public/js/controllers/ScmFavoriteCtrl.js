samyChanApp.controller('ScmFavoriteCtrl', function ($scope, $http, $routeParams, backendUrlGenerator, nav, eventTracker) {
    $scope.favNo = $routeParams.favNo;
    $scope.unselectedChannels = [];
    $scope.selectedChannels = [];
    $scope.hotInstances = {};

    $(document).resize();

    var init = function() {
        loadData();
        nav.setItemActive('fav', $scope.favNo);

        eventTracker.track('FavoriteManager', 'open', $scope.favNo);
    }

    var loadData = function() {
        // load favorites from backend
        $http.get(backendUrlGenerator.buildChannelsUrl()).then(function(res) {
            initData(res.data);
        });
    }

    var initData = function(channels) {
        var favKey = "fav" + $scope.favNo + "sort";

        for (i in channels) {
            if (channels[i][favKey] > 0) {
                channels[i]['favSort'] = channels[i][favKey];
                $scope.selectedChannels.push(channels[i]);
            } else {
                $scope.unselectedChannels.push(channels[i]);
            }
        }

        $scope.selectedChannels.sort(function(a,b) {return a.favSort - b.favSort});

        initHotUnselectedChannels();
        initHotSelectedChannels();
    }

    var reorderSelectedChannels = function() {
        $scope.lastReorderRequestStamp = new Date();
        setTimeout(function() {
            var stamp = new Date();
            if (stamp - $scope.lastReorderRequestStamp < 300) {
                return;
            }

            for (var i = 0; i < $scope.hotInstances.selectedChannels.countRows(); i++) {
                $scope.hotInstances.selectedChannels.setDataAtRowProp(i, 'favSort', i + 1, 'reorder');
            }
        }, 500);
    }

    var initHotUnselectedChannels = function(targetElementSelector, data, isSelectedHot) {
        $scope.hotInstances.unselectedChannels = new Handsontable(document.getElementById('unselected-channels'), {
            columns: [
                {data: "filename", readOnly: true},
                {data: "channelNo", type: "numeric", readOnly: true, className: "channelNo"},
                {data: "name", readOnly: true},
                {data: "scmChannelId", readOnly: true},
                {data: "Move", renderer: function (instance, td, row, col, prop, value, cellProperties) {
                    if (typeof td.rendered == 'undefined') {
                        var a = document.createElement('a');
                        a.appendChild(document.createTextNode("Add"));
                        td.appendChild(a);
                        td.rendered = true;
                        Handsontable.Dom.addEvent(a, 'mousedown', function (e){
                            moveChannel(instance.getDataAtRowProp(row, 'scmChannelId'), instance, row);
                        });

                    }
                    td.setAttribute("class", "text-center");

                    return td;
                }}
            ],
            colHeaders: ["Type", "No", "name", "channelId", "Add"],
            stretchH: 'all',
            multiSelect: false,
            fillHandle: false,
            columnSorting: {
                column: 1,
                sortOrder: true
            },
            data: angular.copy($scope.unselectedChannels),
            multiSelect: false
        });

        // double click event = move channel
        $scope.hotInstances.unselectedChannels.view.wt.update('onCellDblClick', function (row,cell) {
            // if the users clicks double on the add-link, prevent moving the channel
            if (cell.col == 4) {
                return;
            }

            // catch channel-id by the row-index
            var channelId = $scope.hotInstances.unselectedChannels.getDataAtRowProp(cell.row, "scmChannelId");
            moveChannel(channelId, $scope.hotInstances.unselectedChannels, cell.row);
        });

        // filter
        $("#unselected-channels-filter").bind("keyup mouseup", function() {
            var input = $(this);
            setTimeout(function() {
                filterChannel(input.val(), $scope.hotInstances.unselectedChannels, $scope.unselectedChannels);
            }, 1);
        });
    }

    var initHotSelectedChannels = function() {
        $scope.hotInstances.selectedChannels = new Handsontable(document.getElementById("selected-channels"), {
            columns: [
                {data: "favSort", type: "numeric"},
                {data: "name", readOnly: true},
                {data: "scmChannelId", readOnly: true},
                {data: "Move", renderer: function (instance, td, row, col, prop, value, cellProperties) {
                    if (typeof td.rendered == 'undefined') {
                        var a = document.createElement('a');
                        a.appendChild(document.createTextNode("Remove"));
                        td.appendChild(a);
                        td.rendered = true;
                        Handsontable.Dom.addEvent(a, 'mousedown', function (e){
                            moveChannel(instance.getDataAtRowProp(row, 'scmChannelId'), instance, row);
                        });

                    }
                    td.setAttribute("class", "text-center");

                    return td;
                }}
            ],
            rowHeaders: true,
            colHeaders: ["No", "name", "channelId", "Remove"],
            stretchH: 'all',
            columnSorting: false,
            data: angular.copy($scope.selectedChannels),
            multiSelect: false,
            fillHandle: false,
            afterChange: changeCell,
            manualRowMove: true,
            rowHeaders: function(col) {
                return '<i class="fa fa-sort"></i>';
            },
            afterInit: function() {
                $(document).resize();
            },
            afterRowMove: function() {
                reorderSelectedChannels();
            }
        });

        // double click event = move channel
        $scope.hotInstances.selectedChannels.view.wt.update('onCellDblClick', function (row,cell) {

            // if the users clicks double on the remove-link, prevent moving the channel
            if (cell.col == 3) {
                return;
            }

            // catch channel-id by the row-index
            var channelId = $scope.hotInstances.selectedChannels.getDataAtRowProp(cell.row, "scmChannelId");
            moveChannel(channelId, $scope.hotInstances.selectedChannels, cell.row);
        });

        // filter
        $("#selected-channels-filter").bind("keyup mouseup", function() {
            var input = $(this);
            setTimeout(function() {
                filterChannel(input.val(), $scope.hotInstances.selectedChannels, $scope.selectedChannels);
            }, 1);
        });
    }

    var changeCell = function(changes, source) {

        // only listen to edit-events
        if (source != 'edit') {
            return;
        }
        if (changes == null || typeof changes.length == 'undefined' || typeof $scope.hotInstances.selectedChannels == 'undefined') {
            return;
        }

        $.each(changes, function(index, change) {
            if (change[2] == change[3]) {
                return; // nothing has been changed
            }

            $scope.hotInstances.selectedChannels.getData().sort(function(a,b) {return a.favSort - b.favSort});
            $scope.hotInstances.selectedChannels.render();
        });
    }

    var filterChannel = function(searchTerm, hotInstance, data) {
        var newData = $.grep(data, function(item, index) {
            // if we have no search term, we show all channels
            if (searchTerm == null || searchTerm.length == 0) {
                return true;
            }

            if (item["name"] == null) {
                return false;
            }

            return (
                item["name"].toString().toLowerCase().indexOf(searchTerm.toLowerCase()) >= 0 ||
                item["channelNo"].toString() == searchTerm.toLowerCase()
            );
        });

        hotInstance.loadData(newData);
    }

    var moveChannel = function(channelIdToMove, hotInstance, row) {
        var channelIndex;

        // channel is in the UNSELECTED-List
        if (channelIndex = getIndexByScmChannelId(channelIdToMove, $scope.hotInstances.unselectedChannels.getData())) {
            var newChannel = angular.copy($scope.hotInstances.unselectedChannels.getData()[channelIndex]);
            newChannel["favSort"] = $scope.selectedChannels.length + 1;

            // add channel to selectedChannels
            $scope.hotInstances.selectedChannels.getData().push(newChannel);
            $scope.hotInstances.selectedChannels.render();

            // remove channel from unselectedChannels
            hotInstance.alter("remove_row", row);

            // modify the orginal data arrays
            $scope.unselectedChannels.splice(getIndexByScmChannelId(channelIdToMove, $scope.unselectedChannels), 1);
            $scope.selectedChannels.push(newChannel);

            // select last row in the selected-grid
            $scope.hotInstances.selectedChannels.selectCell($scope.selectedChannels.length - 1, 0);

        // channel is in the SELECTED-List
        } else if (channelIndex = getIndexByScmChannelId(channelIdToMove, $scope.hotInstances.selectedChannels.getData())) {
            var newChannel = angular.copy($scope.hotInstances.selectedChannels.getData()[channelIndex]);

            // add channel to selectedChannels
            $scope.hotInstances.unselectedChannels.getData().push(newChannel);
            $scope.hotInstances.unselectedChannels.render();

            // remove channel from the grid
            hotInstance.alter("remove_row", row);

            // modify the orginal data arrays
            $scope.selectedChannels.splice(getIndexByScmChannelId(channelIdToMove, $scope.selectedChannels), 1);
            $scope.unselectedChannels.push(newChannel);

            $scope.hotInstances.unselectedChannels.loadData($scope.hotInstances.unselectedChannels.getData());
            reorderSelectedChannels();
        }

    }

    var getIndexByScmChannelId = function (scmChannelId, channelList) {
        for (var i in channelList) {
            if (channelList[i].scmChannelId == scmChannelId) {
                return i;
            }
        }

        return false;
    }

    /**
     * Save Fav-List
     */
    $scope.save = function() {
        var favChannels = [];

        for (var i = 0; i < $scope.hotInstances.selectedChannels.countRows(); i++) {
            var scmChannelId = $scope.hotInstances.selectedChannels.getDataAtRowProp(i, 'scmChannelId');
            favChannels.push({"scmChannelId": scmChannelId});

        }

        $.blockUI();
        $http.post(backendUrlGenerator.buildFavoriteUrl($scope.favNo), $.param({"scmChannels": favChannels}), {
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).
        success(function(data, status, headers, config) {
            $.unblockUI();
            nav.updateFavCount($scope.favNo, favChannels.length);
        }).
        error(function(data, status, headers, config){
            $.unblockUI();
            }
        );
    }

    init();
});