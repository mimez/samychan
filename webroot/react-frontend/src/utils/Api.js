import apiUrlGenerator from "./apiUrlGenerator";

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
  }
}

export default Api