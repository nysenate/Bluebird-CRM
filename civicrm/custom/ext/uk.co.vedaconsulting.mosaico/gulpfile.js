const gulp = require('gulp');

/**
 * Code block for sass tasks
 */
(function(){
  const sass = require('gulp-sass');
  const cssnano = require('gulp-cssnano');
  const sourcemaps = require('gulp-sourcemaps');
  const autoprefixer = require('gulp-autoprefixer');
  const postcss = require('gulp-postcss');
  const postcssPrefix = require('postcss-prefix-selector');
  const civicrmScssRoot = require('civicrm-scssroot')();

  const bootstrapNamespace = '#bootstrap-theme';

/**
  * Gulp sass task for compiling SASS to css
  */
  gulp.task('sass', ['sass-sync'], () => {
    gulp.src('sass/mosaico-bootstrap.scss')
      .pipe(sourcemaps.init())
      .pipe(sass({
        outputStyle: 'compressed',
        includePaths: civicrmScssRoot.getPath(),
        precision: 10
      }).on('error', sass.logError))
      .pipe(autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false
      }))
      .pipe(postcss([postcssPrefix({
        prefix: bootstrapNamespace + ' ',
        exclude: [/^html/, /^body/, /^.select2-drop-auto-width/, /^div\[ng-controller="PreviewMailingDialogCtrl"\]/]
      })]))
      .pipe(cssnano())
      .pipe(sourcemaps.write('./'))
      .pipe(gulp.dest('./css/'));

    gulp.src('sass/mosaico-crmstar.scss')
      .pipe(sourcemaps.init())
      .pipe(sass({
        outputStyle: 'compressed',
        includePaths: civicrmScssRoot.getPath(),
        precision: 10
      }).on('error', sass.logError))
      .pipe(autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false
      }))
      .pipe(cssnano())
      .pipe(sourcemaps.write('./'))
      .pipe(gulp.dest('./css/'));

    gulp.src('sass/legacy.scss')
      .pipe(sourcemaps.init())
      .pipe(sass({
        outputStyle: 'compressed',
        includePaths: civicrmScssRoot.getPath(),
        precision: 10
      }).on('error', sass.logError))
      .pipe(autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false
      }))
      .pipe(cssnano())
      .pipe(sourcemaps.write('./'))
      .pipe(gulp.dest('./css/'));
  });

  /**
    * Gulp sass task for getting scssRoot for civicrm related config
    */
  gulp.task('sass-sync',() => {
    civicrmScssRoot.updateSync();
  });

  /**
    * Gulp watch task for getting continious watch of sass
    */
  gulp.task('watch', () => {
    gulp.watch('sass/**/*.scss', ['sass']);
  }); 
}());

/**
  * Code block for backstopJS tasks
  */
(function(){
  const _ = require('lodash');
  const argv = require('yargs').argv;
  const backstopjs = require('backstopjs');
  const clean = require('gulp-clean');
  const colors = require('ansi-colors');
  const execSync = require('child_process').execSync;
  const file = require('gulp-file');
  const fs = require('fs');
  const notify = require('gulp-notify');
  const path = require('path');
  const puppeteer = require('puppeteer');
  const PluginError = require('plugin-error');

  const BACKSTOP_DIR = 'tests/backstop_data/';
  const CONFIG_TPL = { 'url': 'http://%{site-host}', 'root': '%{path-to-site-root}' };
  const FILES = {
    siteConfig: path.join(BACKSTOP_DIR, 'site-config.json'),
    temp: path.join(BACKSTOP_DIR, 'backstop.temp.json'),
    tpl: path.join(BACKSTOP_DIR, 'backstop.tpl.json')
  };

  /**
    * Gulp backstop tasks
    * 'backstopjs:reference': For creating reference screenshots
    * 'backstopjs:test': For creating test screenshots and matching them
    * 'backstopjs:openReport': For opening reports in the browser
    * 'backstopjs:approve': Approving reports
    */

  ['reference', 'test', 'openReport', 'approve'].map(action => {
    gulp.task('backstopjs:' + action, () => runBackstopJS(action));
  });

  /**
   * Removes the temp config file and sends a notification
   * based on the given outcome from BackstopJS
   *
   * @param {Boolean} success
   */
  function cleanUpAndNotify (success) {
    gulp
      .src(FILES.temp, { read: false })
      .pipe(clean())
      .pipe(notify({
        message: success ? 'Success' : 'Error',
        title: 'BackstopJS',
        sound: 'Beep'
      }));
  }

  /**
   * Creates the content of the config temporary file that will be fed to BackstopJS
   * The content is the mix of the config template and the list of scenarios
   * under the scenarios/ folder
   *
   * @return {String}
   */
  function createTempConfig () {
    const config = siteConfig();
    const content = JSON.parse(fs.readFileSync(FILES.tpl));

    content.scenarios = _(content.scenarios).map((scenario, index, scenarios) => {
      return _.assign(scenario, {
        cookiePath: path.join(BACKSTOP_DIR, 'cookies', 'admin.json'),
        count: '(' + (index + 1) + ' of ' + scenarios.length + ')',
        url: scenario.url.replace('{url}', config.url)
      });
    })
      .value();

    ['bitmaps_reference', 'bitmaps_test', 'html_report', 'ci_report', 'engine_scripts'].forEach(path => {
      content.paths[path] = BACKSTOP_DIR + content.paths[path];
    });

    return JSON.stringify(content);
  }

  /**
   * Runs backstopJS with the given command.
   *
   * It fills the template file with the list of scenarios, creates a temp
   * file passed to backstopJS, then removes the temp file once the command is completed
   *
   * @param  {String} command
   * @return {Promise}
   */
  function runBackstopJS (command) {
    if (touchSiteConfigFile()) {
      throwError(
        'No site-config.json file detected!\n' +
        `\tOne has been created for you under ${path.basename(BACKSTOP_DIR)}\n` +
        '\tPlease insert the real value for each placeholder and try again'
      );
    }

    return new Promise((resolve, reject) => {
      let success = false;

      gulp.src(FILES.tpl)
        .pipe(file(path.basename(FILES.temp), createTempConfig()))
        .pipe(gulp.dest(BACKSTOP_DIR))
        .on('end', async () => {
          try {
            (typeof argv.skipCookies === 'undefined') && await writeCookies();
            await backstopjs(command, { configPath: FILES.temp, filter: argv.filter });

            success = true;
          } finally {
            cleanUpAndNotify(success);

            success ? resolve() : reject(new Error('BackstopJS error'));
          }
        });
    })
      .catch(function (err) {
        throwError(err.message);
      });
  }

  /**
   * Returns the content of site config file
   *
   * @return {Object}
   */
  function siteConfig () {
    return JSON.parse(fs.readFileSync(FILES.siteConfig));
  }

  /**
   * Creates the site config file is in the backstopjs folder, if it doesn't exists yet
   *
   * @return {Boolean} Whether the file had to be created or not
   */
  function touchSiteConfigFile () {
    let created = false;

    try {
      fs.readFileSync(FILES.siteConfig);
    } catch (err) {
      fs.writeFileSync(FILES.siteConfig, JSON.stringify(CONFIG_TPL, null, 2));

      created = true;
    }

    return created;
  }

  /**
   * A simple wrapper for displaying errors
   * It converts the tab character to the amount of spaces required to correctly
   * align a multi-line block of text horizontally
   *
   * @param {String} msg
   * @throws {Error}
   */
  function throwError (msg) {
    throw new PluginError('Error', {
      message: colors.red(msg.replace(/\t/g, '    '))
    });
  }

  /**
   * Writes the session cookie files that will be used to log in as different users
   *
   * It uses the [`drush uli`](https://drushcommands.com/drush-7x/user/user-login/)
   * command to generate a one-time login url, the browser then go to that url
   * which then creates the session cookie
   *
   * The cookie is then stored in a json file which is used by the BackstopJS scenarios
   * to log in
   *
   * @return {Promise}
   */
  async function writeCookies () {
    const cookiesDir = path.join(BACKSTOP_DIR, 'cookies');
    const cookieFilePath = path.join(cookiesDir, 'admin.json');
    const config = siteConfig();

    const loginUrl = execSync(`drush uli --name=admin --uri=${config.url} --browser=0`, { encoding: 'utf8', cwd: config.root });
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.goto(loginUrl);
    const cookies = await page.cookies();
    await browser.close();

    !fs.existsSync(cookiesDir) && fs.mkdirSync(cookiesDir);
    fs.existsSync(cookieFilePath) && fs.unlinkSync(cookieFilePath);

    fs.writeFileSync(cookieFilePath, JSON.stringify(cookies));
  }
}());


/**
  * Gulp default task
  */
gulp.task('default', ['sass', 'watch']);
