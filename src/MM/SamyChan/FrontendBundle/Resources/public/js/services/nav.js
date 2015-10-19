samyChanApp.factory('nav', function($http, backendUrlGenerator) {

    var nav = {
        items: [],
        scmFiles: {},
        favorites: {},

        activeItem: {type: null, id: null},

        setItemActive: function(type, id) {
            this.activeItem = {type: type, id: id};
            this.buildItems();
        },
        buildItems: function() {
            this.items = [];
            for (var i in this.scmFiles) {
                this.scmFiles[i]["active"] = (this.activeItem.type == "scmFile" && this.activeItem.id == this.scmFiles[i]["scmFileId"]);
                this.items.push(this.scmFiles[i]);
            }

            for (var i in this.favorites) {
                this.favorites[i]["active"] = (this.activeItem.type == "fav" && this.activeItem.id == this.favorites[i]["favNo"]);
                this.items.push(this.favorites[i]);
            }
        },

        loadItems: function(cbFunction) {
            $http.get(backendUrlGenerator.buildPackageUrl()).then(function(res) {
                for (i in res.data.files) {
                    var scmFile = res.data.files[i];
                    scmFile.url = "#/files/" + scmFile.scmFileId;
                    nav.scmFiles[scmFile.scmFileId] = scmFile;
                }

                for (i in res.data.favorites) {
                    var fav = res.data.favorites[i];
                    fav.url = "#/favorites/" + fav.favNo;
                    fav.label = "Fav #" + fav.favNo;
                    nav.favorites[fav.favNo] = fav;
                }

                nav.buildItems();

                cbFunction(nav.items);
            });
        },
    };

    return nav;
});