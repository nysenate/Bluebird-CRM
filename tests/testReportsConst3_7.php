<?php
/*
    Mar 7, 2012
    This test script uses the Reports generator


    1. open sd99
    2. log in
    3. open reports / Constituent Report (Summary) 
    4. report criteria / display columns -> email
    5. check if everything is on its place

    *** check EVERY STEP!
*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'BluebirdSeleniumSettings.php';
require_once 'SampleGenerator.php';


class WebTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/home/mgordo/screenshots';
    protected $screenshotUrl = 'http://localhost/screenshots';

    protected function setUp()
    {

        $this->settings = new BluebirdSeleniumSettings();
        $this->setBrowser($this->settings->browser);
        $this->setBrowserUrl($this->settings->sandboxURL);
        //$this->setSleep($this->settings->sleepTime);
    }
 
    public function testTitle()
    {
        $this->openAndWait('http://sd99/');
        $this->assertTitle('Bluebird');         // make sure Bluebird is open
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
        $this->clickAndWait("link=Constituent Report (Summary)");
        $this->assertTitle('Constituent Report');

        $this->waitForElementPresent("xpath=//table[@class='report-layout display']"); // main table

        // change the Report Criteria
        $this->click("xpath=//form[@id='Summary']/div[2]/div[1]/div[1]/div[1]");
        $this->waitForElementPresent("_qf_Summary_submit"); // Preview report button

        $this->waitForElementPresent("fields[email]");
        $this->click("fields[email]");
        $this->clickAndWait("_qf_Summary_submit"); // Preview report button
        $this->assertTitle('Constituent Report');

        $this->check("Contact Name","xpath=//table[@class='report-layout display']");

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