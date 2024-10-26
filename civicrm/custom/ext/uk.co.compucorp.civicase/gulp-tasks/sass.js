/**
 * @file
 * Exports Gulp "sass" task
 */

'use strict';

var autoprefixer = require('gulp-autoprefixer');
var glob = require('gulp-sass-glob');
var civicrmScssRoot = require('civicrm-scssroot')();
var cleanCSS = require('gulp-clean-css');
var gulp = require('gulp');
var postcss = require('gulp-postcss');
var postcssDiscardDuplicates = require('postcss-discard-duplicates');
var postcssPrefix = require('postcss-prefix-selector');
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var stripCssComments = require('gulp-strip-css-comments');
var sourcemaps = require('gulp-sourcemaps');
var transformSelectors = require('gulp-transform-selectors');

var BOOTSTRAP_NAMESPACE = '#bootstrap-theme';
var OUTSIDE_NAMESPACE_REGEX = /^\.___outside-namespace/;

/**
 * The gulp task compiles and minifies scss/civicase.scss file into css/civicase.min.css.
 * Also prefix the output css selector with `#bootstrap-theme` selector except the output.
 * selector starts from either `body`, `page-civicrm-case` or `.___outside-namespace` classes.
 *
 * @returns {object} stream
 */
function sassTask () {
  return gulp.src('scss/civicase.scss')
    .pipe(glob())
    .pipe(sourcemaps.init())
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: civicrmScssRoot.getPath(),
      precision: 10
    }).on('error', sass.logError))
    .pipe(stripCssComments({ preserve: false }))
    .pipe(postcss([postcssPrefix({
      prefix: BOOTSTRAP_NAMESPACE + ' ',
      exclude: [/^body/, /page-civicrm-case/, OUTSIDE_NAMESPACE_REGEX]
    }), postcssDiscardDuplicates]))
    .pipe(transformSelectors(removeOutsideNamespaceMarker, { splitOnCommas: true }))
    .pipe(cleanCSS({ sourceMap: true }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('css/'));
}

/**
 * Deletes the special class that was used as marker for styles that should
 * not be nested inside the bootstrap namespace from the given selector
 *
 * @param  {string} selector selector
 * @returns {string} replaced string
 */
function removeOutsideNamespaceMarker (selector) {
  return selector.replace(OUTSIDE_NAMESPACE_REGEX, '');
}

module.exports = sassTask;
