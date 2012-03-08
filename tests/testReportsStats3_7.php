<?php
/*
    Mar 7, 2012
    This test script uses the Reports generator


    1. open sd99
    2. log in
    3. open reports / district stats
    4. check if everything is on its place

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
        $this->clickAndWait("link=District Stats");
        $this->assertTitle('District Stats');

        $this->waitForElementPresent("ContactTypes"); // main table
        $this->waitForElementPresent("help");         // help area
        $this->waitForElementPresent("districts");    // districts block

        $this->check("Contact Counts","xpath=//div[@id='ContactTypes']/table[1]");
        $this->check("Email Counts","xpath=//div[@id='ContactTypes']/table[2]");
        $this->check("Miscellaneous Counts","xpath=//div[@id='ContactTypes']/table[3]");

        $this->check("Senate Districts","SenateDistricts");
        $this->click("SenateDistricts");

        $this->check("Assembly Districts","AssemblyDistricts");
        $this->click("AssemblyDistricts");

        $this->check("Congressional Districts","CongressionalDistricts");
        $this->click("CongressionalDistricts");

        $this->check("Election Districts","ElectionDistricts");
        $this->click("ElectionDistricts");

        $this->check("Town/Assembly/Election","TownADED");
        $this->click("TownADED");

        $this->check("Counties","Counties");
        $this->click("Counties");

        $this->check("Towns","Towns");
        $this->click("Towns");

        $this->check("Wards","Wards");
        $this->click("Wards");

        $this->check("School Districts","Schools");
        $this->click("Schools");

        $this->check("Zip Codes","Zip");
        $this->click("Zip");

        $this->check("Issue Codes","IssueCodes");
        $this->click("IssueCodes");

        $this->check("(contacts)","Keywords");
        $this->click("Keywords");

        $this->check("(activities)","aKeywords");
        $this->click("aKeywords");

        $this->check("(cases)","cKeywords");
        $this->click("cKeywords");

        $this->check("Legislative Positions","Positions");
        $this->click("Positions");

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