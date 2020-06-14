import apiUrlGenerator from "./apiUrlGenerator";
/*@todo: error-handling implementieren, z.B. ob der result auch json ist usw....*/
var Api = {
  getPackage(scmPackageId, callback) {
    fetch(apiUrlGenerator.buildPackageUrl(scmPackageId))
      .then(results => {
        return results.json()
      })
      .then(data => {
        callback(data)
      })
  },

  getFavorites(scmPackageId, favNo, callback) {
    fetch(apiUrlGenerator.buildFavoriteUrl(scmPackageId, favNo))
      .then(results => {
        return results.json()
      })
      .then(data => {
        callback(data)
      })
  },

  getFile(scmPackageHash, scmFileId, callback) {
    fetch(apiUrlGenerator.buildFileUrl(scmPackageHash, scmFileId))
      .then(results => {
        if (!results.ok) {
          throw Error(results.statusText);
        }
        return results;
      })
      .then(results => {
        return results.json()
      })
      .then(data => {
        callback(data)
      })
      .catch(error => {
        console.log(error)
      })
  }
}

export default Api