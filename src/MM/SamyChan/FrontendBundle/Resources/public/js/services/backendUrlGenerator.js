samyChanApp.factory('backendUrlGenerator', function() {
    var scmPackageHash = $("body").data("scm-package-hash");

    return {
        buildFileUrl: function(scmFileId) {
            return "/backend/" + scmPackageHash + "/file/" + scmFileId + "/json/";
        },
        buildPackageUrl: function() {
            return "/backend/" + scmPackageHash + ".json";
        },
        buildReorderUrl: function(scmFileId) {
            return "/backend/" + scmPackageHash + "/file/" + scmFileId + "/reorder/";
        },
        buildFavoriteUrl: function(favNo) {
            return "/backend/" + scmPackageHash + "/favorites/" + favNo + ".json";
        },
        buildChannelsUrl: function() {
            return "/backend/" + scmPackageHash + "/channels.json";
        },
        buildDownloadUrl: function() {
            return "/backend/" + scmPackageHash + "/download/";
        },
        buildUploadUrl: function() {
            return "/backend/upload/";
        }
    }
});