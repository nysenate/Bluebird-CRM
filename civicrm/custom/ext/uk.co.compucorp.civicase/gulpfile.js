/**
 * @file
 * Contains gulp tasks for the application
 *
 * Available Tasks
 * default
 * sass:sync
 * sass
 * test
 * watch
 */

'use strict';

var gulp = require('gulp');

var sassTask = require('./gulp-tasks/sass.js');
var sassSyncTask = require('./gulp-tasks/sass-sync.js');
var testTask = require('./gulp-tasks/karma-unit-test.js');
var watchTask = require('./gulp-tasks/watch.js');

/**
 * Updates and sync the scssRoot paths
 */
gulp.task('sass:sync', sassSyncTask);

/**
 * Compiles civicase.scss under scss folder to CSS counterpart
 */
gulp.task('sass', gulp.series('sass:sync', sassTask));

/**
 * Runs Karma unit tests
 */
gulp.task('test', testTask);

/**
 * Watches for scss and js file changes and run sass task and karma unit tests
 */
gulp.task('watch', watchTask);

/**
 * Runs sass and test task
 */
gulp.task('default', gulp.series('sass', 'test'));
