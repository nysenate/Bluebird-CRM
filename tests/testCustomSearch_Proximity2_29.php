<?php /* 

    Feb 29, 2012
    This test script uses the Custom Search
    Find everybody within the 50 miles distance
    from the Zip-Code 12247

    1. open sd99
    2. log in
    3. open custom search / proximity search
    4. set parameters Distance and Postal Code
    5. run search 

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
        $this->type('distance','50');
        $this->type('postal_code','12247');
        $this->click('_qf_Custom_refresh-bottom');
        $this->waitForPageToLoad('30000');
        
        $this->assertTitle('Proximity Search');
        $this->waitForElementPresent('search-status');
        $this->assertTrue($this->isTextPresent("Selected records only"),"Custom Search: Contacts not found in the database ");
    }

    private function openCustomSearch() {
        // CUSTOM SEARCH actually is not a link
        $this->click("xpath=//ul[@id='nyss-menu']/li[2]"); // using xpath to find the CUSTOM SEARCH menu
        $this->waitForElementPresent('link=Proximity Search'); // wait until menu opens
        $this->click('link=Proximity Search');  // click the link
        $this->waitForPageToLoad('30000');  
        $this->waitForElementPresent('_qf_Custom_refresh-bottom'); // wain until SEARCH button present
    }

    private function stop() {
        $this->waitForElementPresent('NonExistentElement');
    }


}
?>