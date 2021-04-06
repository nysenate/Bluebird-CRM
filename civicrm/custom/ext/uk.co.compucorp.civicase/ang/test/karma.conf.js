var cv = require('civicrm-cv')({ mode: 'sync' });

module.exports = function (config) {
  var civicrmPath = cv("path -d '[civicrm.root]'")[0].value;
  var extPath = cv('path -x uk.co.compucorp.civicase')[0].value;
  var shoreditch = cv('path -x org.civicrm.shoreditch')[0].value;

  config.set({
    basePath: civicrmPath,
    frameworks: ['jasmine', 'jasmine-diff'],
    files: [
      // the global dependencies
      'bower_components/jquery/dist/jquery.min.js',
      'bower_components/jquery-ui/jquery-ui.js',
      'bower_components/lodash-compat/lodash.min.js',
      'bower_components/select2/select2.min.js',
      'bower_components/jquery-validation/dist/jquery.validate.min.js',
      'packages/jquery/plugins/jquery.blockUI.js',
      'js/Common.js',

      'bower_components/angular/angular.min.js',
      'bower_components/angular-mocks/angular-mocks.js',
      'bower_components/angular-route/angular-route.min.js',
      'bower_components/angular-bootstrap/ui-bootstrap.min.js',
      'bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js',

      shoreditch + '/base/js/affix.js',
      extPath + '/packages/moment.min.js',
      extPath + '/packages/simplebar.min.js',

      // Global variables that need to be accessible in the test environment
      extPath + '/ang/test/global.js',

      // angular templates
      extPath + '/ang/civicase/**/*.html',

      // Source Files
      extPath + '/ang/civicase-base.js',
      extPath + '/ang/civicase.js',
      extPath + '/ang/workflow.js',
      { pattern: extPath + '/ang/civicase-base/**/*.js' },
      { pattern: extPath + '/ang/civicase/**/*.js' },
      { pattern: extPath + '/ang/workflow/**/*.js' },

      // Spec files
      { pattern: extPath + '/ang/test/mocks/modules.mock.js' },
      { pattern: extPath + '/ang/test/**/mocks/*.module.mock.js' },
      { pattern: extPath + '/ang/test/**/mocks/**/*.js' },
      { pattern: extPath + '/ang/test/civicase/**/*.js' },
      { pattern: extPath + '/ang/test/civicase-base/**/*.js' },
      { pattern: extPath + '/ang/test/workflow/**/*.js' }
    ],
    exclude: [
    ],
    // Used to transform angular templates in JS strings
    preprocessors: (function (obj) {
      obj[extPath + '/ang/civicase/**/*.html'] = ['ng-html2js'];
      obj[extPath + '/ang/civicase/**/*.js'] = ['coverage'];
      obj[extPath + '/ang/civicase-base/**/*.js'] = ['coverage'];
      obj[extPath + '/ang/workflow/**/*.js'] = ['coverage'];

      return obj;
    })({}),
    ngHtml2JsPreprocessor: {
      stripPrefix: '.*uk.co.compucorp.civicase/ang',
      prependPrefix: '~',
      moduleName: 'civicase.templates'
    },
    reporters: ['progress', 'coverage'],
    // web server port
    port: 9876,
    colors: true,
    logLevel: config.LOG_INFO,
    autoWatch: true,
    coverageReporter: {
      dir: extPath + '/coverage',
      reporters: [
        { type: 'text-summary' },
        { type: 'lcov', subdir: 'js-lcov' }
      ]
    },
    browsers: ['ChromeHeadlessBrowser'],
    customLaunchers: {
      ChromeHeadlessBrowser: {
        base: 'ChromeHeadless',
        flags: [
          '--no-sandbox',
          '--disable-dev-shm-usage'
        ]
      }
    }
  });
};
