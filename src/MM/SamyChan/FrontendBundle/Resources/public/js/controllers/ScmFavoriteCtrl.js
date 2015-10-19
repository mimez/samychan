samyChanApp.controller('ScmFavoriteCtrl', function ($scope, $http, $routeParams, backendUrlGenerator, nav) {
    $scope.favNo = $routeParams.favNo;
    $scope.unselectedChannels = [];
    $scope.selectedChannels = [];
    $scope.hotInstances = {};

    $(document).resize();

    var init = function() {
        loadData();
        nav.setItemActive('fav', $scope.favNo);
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
            if (stamp - $scope.lastReorderRequestStamp < 100) {
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
                {data: "filename", editor: false},
                {data: "channelNo", type: "numeric", editor: false, className: "channelNo"},
                {data: "name", editor: false},
                {data: "scmChannelId", editor: false},
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
            columnSorting: {
                column: 1,
                sortOrder: true
            },
            data: $scope.unselectedChannels,
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
        $("#unselected-channels-filter").keyup(function() {
            filterChannel($(this).val(), $scope.hotInstances.unselectedChannels, $scope.unselectedChannels);
        });
    }

    var initHotSelectedChannels = function() {
        window.hot = $scope.hotInstances.selectedChannels = new Handsontable(document.getElementById("selected-channels"), {
            columns: [
                {data: "favSort", type: "numeric"},
                {data: "name", editor: false},
                {data: "scmChannelId", editor: false},
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
            data: $scope.selectedChannels,
            multiSelect: false,
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
        $("#selected-channels-filter").keyup(function() {
            filterChannel($(this).val(), $scope.hotInstances.selectedChannels, $scope.selectedChannels);
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

            $scope.selectedChannels.sort(function(a,b) {return a.favSort - b.favSort});
            $scope.hotInstances.selectedChannels.render();

            /*reorderSelectedChannels();*/
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

        if (channelIndex = getIndexByScmChannelId(channelIdToMove, $scope.unselectedChannels)) {
            var newChannel = angular.copy($scope.unselectedChannels[channelIndex]);
            newChannel["favSort"] = $scope.selectedChannels.length + 1;
            $scope.selectedChannels.push(newChannel);
            $scope.hotInstances.selectedChannels.loadData($scope.selectedChannels);
            //$scope.hotInstances.unselectedChannels.sort(1, true); // force sort in unselected channels
            hotInstance.alter("remove_row", row);
            $scope.hotInstances.selectedChannels.selectCell($scope.selectedChannels.length - 1, 0);

        } else if (channelIndex = getIndexByScmChannelId(channelIdToMove, $scope.selectedChannels)) {
            var newChannel = angular.copy($scope.selectedChannels[channelIndex]);
            $scope.unselectedChannels.push(newChannel);
            hotInstance.alter("remove_row", row);
            $scope.hotInstances.unselectedChannels.loadData($scope.unselectedChannels);
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
        $.post(backendUrlGenerator.buildFavoriteUrl($scope.favNo), {"scmChannels": favChannels}).
            success(function(data, status, headers, config) {
                $.unblockUI();
            }).
            error(function(data, status, headers, config){
                $.unblockUI();
            }
        );
    }

    init();
});