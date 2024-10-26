/**
 * @file
 * Exports Gulp "sass:sync" task
 */

var civicrmScssRoot = require('civicrm-scssroot')();

module.exports = function (done) {
  civicrmScssRoot.updateSync();
  done();
};
