<?php
/*
    Mar 5, 2012
    This test script uses the Advanced Search
    Find the contact named Mike Gordo
    Add Senior tag

    1. open sd99
    2. log in
    3. open advanced search
    4. run search by name=Mike Gordo
    5. open first found contact 
    6. set the tag Senior
    7. save contact
    8. open advanced search
    9. run search by name=Mike Gordo
    10. open found contact and edit it
    11. remove the tag
    12. save contact

    *** check EVERY STEP!
    *** NOTE: Individual SHOULD NOT have any tags!
*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'SampleGenerator.php';
require_once 'Config.php';


class WebTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/home/mgordo/screenshots';
    protected $screenshotUrl  = 'http://localhost/screenshots';
 
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
        $this->openAdvancedSearch();

        $keyword = getSearchName();                // Config.php
        $this->type('sort_name',$keyword);
        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('30000');
        $this->assertTitle('Advanced Search');
        $this->assertTrue(!$this->isTextPresent("No matches"),"Advanced Search: Contact is not found in the database ");

        // click on the first result
        $this->click("xpath=//table[@class='selector crm-row-highlighter-processed']/tbody[1]/tr[1]/td[3]/a"); 
        $this->waitForPageToLoad('30000');
        $this->assertTitle("$keyword"); // check that right page is open

        // find Tags and click on it
        $this->waitForElementPresent("xpath=//li[@id='tab_tag']/a[1]");
        $this->click("xpath=//li[@id='tab_tag']/a[1]");

        // find TagTree and choose Senior
        $this->waitForElementPresent("tagtree");
        $this->click("xpath=//li[@id='tag_5']/ins[1]");
        $this->click("check_46");

        // find Summary and click on it
        $this->waitForElementPresent("xpath=//li[@id='tab_summary']/a[1]");
        $this->click("xpath=//li[@id='tab_summary']/a[1]");

        $this->assertTrue(!$this->isTextPresent("Tags 0"),"Can not set the tag ");

        // now REMOVE the tag

        // find Tags and click on it
        $this->waitForElementPresent("xpath=//li[@id='tab_tag']/a[1]");
        $this->click("xpath=//li[@id='tab_tag']/a[1]");

        // find TagTree and unchoose Senior
        $this->waitForElementPresent("tagtree");
        $this->click("check_46");

        // find Summary and click on it
        $this->waitForElementPresent("xpath=//li[@id='tab_summary']/a[1]");
        $this->click("xpath=//li[@id='tab_summary']/a[1]");

        $this->assertTrue($this->isTextPresent("Tags 0"),"Can not remove the tag ");

    }

    private function openAdvancedSearch() {
        // ADVANCED SEARCH actually is not a link
        // AND its content loads dynamically
        $this->click('class=civi-advanced-search-link');
        $this->waitForElementPresent('_qf_Advanced_refresh');
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