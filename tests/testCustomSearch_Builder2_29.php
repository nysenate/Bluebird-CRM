<?php

/*
    Feb 29, 2012
    This test script uses the Custom Search / search builder
    Search for the individuals using this request individual / display name / LIKE / j%

    1. open sd99
    2. log in
    3. open custom search / search builder
    4. set parameters Distance and Postal Code
    5. run search 

*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'BluebirdSeleniumSettings.php';
require_once 'SampleGenerator.php';
require_once 'Config.php';


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
        $this->openAndWait(getMainURL());
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
        $this->openCustomSearch();

        $this->click('mapper[1][0][0]');
        $this->select('mapper[1][0][0]', "value=Individual"); // select individual in first dropbox
        $this->waitForElementPresent('mapper[1][0][1]'); // wain until this element appears

        $this->click('mapper[1][0][1]');
        $this->select('mapper[1][0][1]', "value=display_name"); // select Display Name

        $this->click('operator_1_0');
        $this->select('operator_1_0', "value=LIKE"); // select operator

        $this->click('value_1_0');
        $this->type('value_1_0', "j%"); // type search query

        $this->click('_qf_Builder_refresh');
        $this->waitForPageToLoad('30000');
        
        $this->assertTitle('Search Builder');
        $this->assertTrue($this->isTextPresent("Selected records only"),"Custom Search: Contacts not found in the database ");
    }

    private function openCustomSearch() {
        // CUSTOM SEARCH actually is not a link
        $this->click("xpath=//ul[@id='nyss-menu']/li[2]"); // using xpath to find the CUSTOM SEARCH menu
        $this->waitForElementPresent('link=Search Builder'); // wait until menu opens
        $this->click('link=Search Builder');  // click the link
        $this->waitForPageToLoad('30000');  
        $this->waitForElementPresent('_qf_Builder_refresh'); // wain until SEARCH button present
    }

    private function stop() {
        $this->waitForElementPresent('NonExistentElement');
    }


}
?>