export default {
    buildFileUrl: function(scmPackageHash, scmFileId) {
        return "/backend/" + scmPackageHash + "/file/" + scmFileId + "/json/";
    },

    buildFileExportUrl: function(scmPackageHash, scmFileId) {
        return "/backend/" + scmPackageHash + "/file/" + scmFileId + "/csv/";
    },

    buildPackageUrl: function(scmPackageHash) {
        return "http://samychan.devbox.local/backend/" + scmPackageHash + ".json";
    },

    buildReorderUrl: function(scmPackageHash, scmFileId) {
        return "/backend/" + scmPackageHash + "/file/" + scmFileId + "/reorder/";
    },

    buildFavoriteUrl: function(scmPackageHash, favNo) {
        return "http://samychan.devbox.local/backend/" + scmPackageHash + "/favorites/" + favNo + ".json";
    },

    buildChannelsUrl: function(scmPackageHash) {
        return "/backend/" + scmPackageHash + "/channels.json";
    },

    buildDownloadUrl: function(scmPackageHash) {
        return "/backend/" + scmPackageHash + "/download/";
    },

    buildUploadUrl: function() {
        return "/backend/upload/";
    },

    buildUploadJsonUrl: function() {
        return "/backend/upload.json";
    },

    buildImportSettingsUrl: function(scmPackageHash) {
        return "/backend/" + scmPackageHash + "/import-settings.json";
    }
}
