<?php /*

    Feb 29, 2012
    This test script checks if all the menus exisis on the Dashboard

    1. open sd99
    2. log in
    3. check search boxes and top menus

*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'SampleGenerator.php';
require_once 'Config.php';


class WebTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected $captureScreenshotOnFailure = FALSE;
    protected $screenshotPath = '';
    protected $screenshotUrl = 'http://localhost/screenshots';

    protected function setUp()
    {
        $this->settings = new BluebirdSeleniumSettings();
        $this->setBrowser($this->settings->browser);
        $this->setBrowserUrl($this->settings->sandboxURL);

        if (strpos($this->settings->browser,"firefox")) {
            $this->captureScreenshotOnFailure = TRUE;
            $this->screenshotPath = getScreenshotPath();
        }
        //$this->setSleep($this->settings->sleepTime);
    }

    public function testTitle()
    {
        $myurl = getMainURL();

        if (strpos($this->settings->browser,"explore")) {
            $myurl_ie=$myurl.'/logout';                              //IE has problems closing the session
            $this->openAndWait($myurl_ie);
        }

        $this->openAndWait($myurl);
        $this->assertTitle(getMainURLTitle());         // make sure Bluebird is open
        $this->webtestLogin();
        $this->performTasks();
    }

/*
    This function logs in to Bluebird using standard Username and Password
*/
    public function webtestLogin() {
        //$this->open("{$this->sboxPath}user");
        $password = $this->settings->adminPassword;
        $username = $this->settings->adminUsername;
        // Make sure login form is available
        $this->waitForElementPresent('edit-submit');
        $this->type('edit-name', $username);
        $this->type('edit-pass', $password);
        $this->click('edit-submit');
        $this->waitForPageToLoad('30000');
    }

/*
    This function contains call for all other functions
*/
    public function performTasks() {
        $this->setSleep($this->settings->sleepTime);
        $this->assertTitle('Bluebird Home');

        $this->check("ADVANCED SEARCH","class=civi-advanced-search-link");          // advanced search
        $this->check("Custom Search","xpath=//ul[@id='nyss-menu']/li[2]");          // custom search
        $this->check("Reports","xpath=//ul[@id='nyss-menu']/li[3]");                // reports
        $this->check("Manage","xpath=//ul[@id='nyss-menu']/li[4]");                 // manage
//        $this->check("Administer","xpath=//ul[@id='nyss-menu']/li[5]");             // Administer

        $this->check("Refresh Dashboard Data","xpath=//a[@class='button show-refresh']/span[1]");  // Refresh Dashboard Data
        $this->check("Find Contacts","civi_sort_name");     // Find contacts
        $this->check("Find Anything!","civi_text_search");   // Find Anything!

        $this->check("CREATE","create-link");   // create

        $this->check("Dashboard","dashboard-link-wrapper");   // bottom Dashboard link

    }

/*
    Function checks if element with Name=$name and Id(xpath)=$id is on the page
*/
    private function check($name, $id) {
        if ($name=='' || $id=='') return 0;
        $this->assertTrue($this->isTextPresent("$name"),"\"$name\" not found on the page\n ");
        $this->waitForElementPresent("$id");
    }

    private function stop() {
        $this->waitForElementPresent('NonExistentElement');
    }


}
?>
