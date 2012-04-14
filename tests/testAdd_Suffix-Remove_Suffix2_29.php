<?php 
/* 

    Feb 29, 2012
    This test script uses the Advanced Search
    Find the contact named Mike Gordo
    Add suffix

    1. open sd99
    2. log in
    3. open advanced search
    4. run search by name=Mike Gordo
    5. open found contact and edit it
    6. add suffix = PhD
    7. save contact
    8. open advanced search
    9. run search by name=Mike Gordo
    10. open found contact and edit it
    11. remove suffix = PhD
    12. save contact

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

        $keyword = getSearchName();                  // Config.php
        $this->type('sort_name',$keyword);
        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('30000');
        $this->assertTitle('Advanced Search');
        $this->assertTrue($this->isTextPresent("Select Records"),"Advanced Search: Contact is not found in the database ");

        // click on the first result
        $this->click("xpath=//table[@class='selector crm-row-highlighter-processed']/tbody[1]/tr[1]/td[3]/a"); 
        $this->waitForPageToLoad('30000');


        // find EDIT
        $this->waitForElementPresent("xpath=//ul[@id='actions']/li[2]/a[1]");
        // and click it!
        $this->click("xpath=//ul[@id='actions']/li[2]/a[1]");

        $this->waitForPageToLoad('30000');

        // wait for SAVE to present
        $this->waitForElementPresent("_qf_Contact_upload_view-bottom");

        // now we can edit the contact
        $this->click("suffix_id");              // click on the suffix dropbox
        $this->select("suffix_id","value=12");  // select that element ( SUFFIX = PhD )

        $this->click("_qf_Contact_upload_view-bottom"); // save
        $this->waitForPageToLoad('30000');

        $this->assertTrue($this->isTextPresent(", PhD"),"Can not add suffix='PhD' ");

        // now REMOVE the suffix!

        $this->openAdvancedSearch();
        $this->type('sort_name',$keyword);
        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('30000');
        $this->assertTitle('Advanced Search');
        $this->assertTrue($this->isTextPresent("Select Records"),"Advanced Search: Contact is not found in the database ");

        // click on the first result
        $this->click("xpath=//table[@class='selector crm-row-highlighter-processed']/tbody[1]/tr[1]/td[3]/a"); 
        $this->waitForPageToLoad('30000');


        // find EDIT
        $this->waitForElementPresent("xpath=//ul[@id='actions']/li[2]/a[1]");
        // and click it!
        $this->click("xpath=//ul[@id='actions']/li[2]/a[1]");

        $this->waitForPageToLoad('30000');

        // wait for SAVE to present
        $this->waitForElementPresent("_qf_Contact_upload_view-bottom");

        // now we can edit the contact
        $this->click("suffix_id");              // click on the suffix dropbox
        $this->select("suffix_id","value=");  // select nothing!

        $this->click("_qf_Contact_upload_view-bottom"); // save
        $this->waitForPageToLoad('30000');

        $this->assertTrue(!$this->isTextPresent("$keyword, PhD"),"Can not remove suffix='PhD' ");

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