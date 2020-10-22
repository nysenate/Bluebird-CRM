/**
 * @file
 * Contains gulp tasks for the application
 *
 * Available Tasks
 * backstopjs:reference
 * backstopjs:test
 * backstopjs:openReport
 * backstopjs:approve
 * watch
 */

'use strict';

var gulp = require('gulp');

var backstopJSTask = require('./gulp-tasks/backstopjs.js');

/**
 * BackstopJS task
 *
 * backstopjs:reference: Creates reference screenshots
 * backstopjs:test: Creates test screenshots and matching them
 * backstopjs:openReport: Opens reports in the browser
 * backstopjs:approve: Approves reports
 */
['reference', 'test', 'openReport', 'approve'].map(backstopJSTask.defineAction);

/**
 * Setups required BackstopJS data.
 */
gulp.task('backstopjs:setup-data', backstopJSTask.setupData);
