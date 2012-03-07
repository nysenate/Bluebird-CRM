<?php

/*
    Feb 29, 2012
    This test script uses the Custom Search
    Find every person born in January 1990 or earlier

    1. open sd99
    2. log in
    3. open custom search / birthday search
    4. set parameters Distance and Postal Code
    5. run search 

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
        $this->openCustomSearch();

        $this->click('birth_month');
        $this->select('birth_month', "value=1"); //1 is January in html source code

        $this->type('year_end','1990');

        $this->click('_qf_Custom_refresh-bottom');
        $this->waitForPageToLoad('30000');
        
        $this->assertTitle('Birthday Search');
        $this->assertTrue($this->isTextPresent("Selected records only"),"Custom Search: Contacts not found in the database ");
    }

    private function openCustomSearch() {
        // CUSTOM SEARCH actually is not a link
        $this->click("xpath=//ul[@id='nyss-menu']/li[2]"); // using xpath to find the CUSTOM SEARCH menu
        $this->waitForElementPresent('link=Proximity Search'); // wait until menu opens
        $this->click('link=Birthday Search');  // click the link
        $this->waitForPageToLoad('30000');  
        $this->waitForElementPresent('_qf_Custom_refresh-bottom'); // wain until SEARCH button present
    }

    private function stop() {
        $this->waitForElementPresent('NonExistentElement');
    }


}
?>