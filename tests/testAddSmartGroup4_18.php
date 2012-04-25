<?php 
/* 
    Apr 18, 2012
    This test script uses the Advanced Search
    Find contacts by name and form the new smart group

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

        // only individuals
        $this->click("crmasmSelect0");
        $this->select("crmasmSelect0", "value=Individual");

        // choose city = Albany
        $this->type("city","Albany");

        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('30000');
        $this->assertTitle('Advanced Search');
        $this->assertTrue(!$this->isTextPresent("No matches"),"Advanced Search: Contact is not found in the database ");

        // select all of the results on first page
        $this->waitForElementPresent("toggleSelect");
        $this->click("toggleSelect"); 
        
        // select New Smart Group
        $this->select("task","value=13");

        //click Go
        $this->click("Go");
        $this->waitForPageToLoad('30000');

        // select Create New Group
        $this->waitForElementPresent("title");
        $this->type("title","Smart Group of ".$keyword."s");
        $this->type("description","This smart group was created by the test script.");

        // click Add To Group
        $this->waitForElementPresent("_qf_SaveSearch_next-bottom");
        $this->click("_qf_SaveSearch_next-bottom");
        $this->waitForPageToLoad('30000');

        $this->assertTrue($this->isTextPresent("has been saved"),"Error: Script could not add contacts to the smart group ");

        $this->click("xpath=//ul[@id='nyss-menu']/li[4]"); // using xpath to find the Manage menu
        $this->waitForElementPresent('link=Manage Groups'); // wait until menu opens
        $this->click('link=Manage Groups');  // click the link
        $this->waitForPageToLoad('30000');

        // delete group
        $this->waitForElementPresent('title'); 
        $this->type('title',$groupname);
        $this->waitForElementPresent('_qf_Search_refresh');
        $this->click('_qf_Search_refresh');

        // click on more->
        $this->waitForElementPresent('crm-group-selector');
        $this->click("xpath=//table[@id='crm-group-selector']/tbody[1]/tr[1]/td[6]/span[2]");
        // click on Delete
        $this->click("xpath=//span[@class='btn-slide']/ul[@class='panel']/li[1]/a[1]");
        $this->waitForPageToLoad('30000');

        $this->waitForElementPresent('_qf_Edit_next-bottom');
        $this->click('_qf_Edit_next-bottom');
        $this->waitForPageToLoad('30000');
        
        $this->assertTrue($this->isTextPresent("deleted"),"Error: Script could not delete the group ");
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