/**
 * @file
 * Exports Gulp "test" task
 */

'use strict';

var karma = require('karma');
var path = require('path');

module.exports = function (done) {
  new karma.Server({
    configFile: path.resolve(__dirname, '../ang/test/karma.conf.js'),
    singleRun: true
  }, done).start();
};
