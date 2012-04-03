<?php /* 

    Mar 7, 2012
    This test script uses the Reports generator


    1. open sd99
    2. log in
    3. open reports / Activity Report
    4. Display column Subject
    5. Set filter TYPE = Meeting
    6. check if everything is on its place

    *** check EVERY STEP!
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
            $myurl.='/logout';                              //IE has problems closing the session
        }

        $this->openAndWait($myurl);
        $this->assertTitle(getMainURLTitle());         // make sure Bluebird is open
        $this->webtestLogin();
        $this->performTasks();
    }

/*
    This function logs in to Bluebird using standard Username and Password
    see *BluebirdSeleniumSettings.php*
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
        $this->openReports();
        $this->clickAndWait("link=Activity Report");
        $this->assertTitle('Activity Report');

        $this->assertTrue(!$this->isTextPresent("No results found."),"Activity report: no results found");

        $this->waitForElementPresent("xpath=//form[@id='Activity']/div[2]/div[1]/div[1]/div[1]"); // report criteria
        $this->click("xpath=//form[@id='Activity']/div[2]/div[1]/div[1]/div[1]"); // report criteria

        $this->waitForElementPresent("_qf_Activity_submit"); // Preview Report button

        // change the Report Criteria

        $this->waitForElementPresent("fields[activity_subject]");
        $this->click("fields[activity_subject]");

        $this->clickAndWait("_qf_Activity_submit"); // click Preview Report

        $this->assertTitle('Activity Report');
        $this->check("Added By","xpath=//table[@class='report-layout display']");

        $this->assertTrue(!$this->isTextPresent("No results found."),"Activity report: no results found");

     }

    private function openReports() {
        $this->click("xpath=//ul[@id='nyss-menu']/li[3]");
        $this->waitForElementPresent("xpath=//div[@class='menu-div outerbox']");
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