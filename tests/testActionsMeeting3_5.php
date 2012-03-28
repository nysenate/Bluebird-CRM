<?php 

/* 
    Mar 5, 2012
    This test script uses the Advanced Search
    Find the contact named Mike Gordo
    set up the meeting

    1. open sd99
    2. log in
    3. open advanced search
    4. run search by name=Mike Gordo
    5. open first found contact 
    6. click on Actions / meeting
    7. add meeting at 10 am tomorrow, key word Aging
    8. save contact
    9. delete the meeting

    *** check EVERY STEP!
*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'SampleGenerator.php';
require_once 'Config.php';


class WebTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected $captureScreenshotOnFailure = FALSE;
    protected $screenshotPath = getScreenshotPath();
    protected $screenshotUrl = 'http://localhost/screenshots';
 
    protected function setUp()
    {
        $this->settings = new BluebirdSeleniumSettings();
        $this->setBrowser($this->settings->browser);
        $this->setBrowserUrl($this->settings->sandboxURL);

        if (strpos($this->settings->browser,"firefox")) {
            $this->captureScreenshotOnFailure = TRUE;
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
        $this->openAdvancedSearch();
        $keyword = getSearchName();                   // Config.php
        $this->searchAndOpen($keyword);

        // find Actions and click on it
        $this->waitForElementPresent("xpath=//div[@id='crm-contact-actions-link']");
        $this->click("xpath=//div[@id='crm-contact-actions-link']");

        // menu opens
        // find Meeting and click on it
        $this->click("link=Meeting");
        $this->waitForPageToLoad('30000');
        $this->assertTitle($keyword);
        $this->waitForElementPresent("_qf_Activity_upload-bottom");

        // edit date. set tomorrow
        $tomorrow = date('m/d/Y',mktime()+86400);
        $this->click("activity_date_time_display");
        $dt = explode("/", $tomorrow);
        $month = $dt[0]-1;
        $day = $dt[1]-0;
        $year = $dt[2];
        $this->select("class=ui-datepicker-month","value=$month");
        $this->select("class=ui-datepicker-year","value=$year");
        $this->click("link=$day");

        $this->type("activity_date_time_time","10:00AM");
        $this->type("duration","45");
        $this->type("subject","Really Big Meeting");

        // key word Aging
        $this->click('token-input-activity_taglist_296');
        $this->waitForElementPresent('class=token-input-dropdown-facebook'); // dropdown element Begin typing a tag name

        $keyword2 = "Aging";
        $this->typeKeys('token-input-activity_taglist_296',$keyword2); // type the keyword
        $this->waitForElementPresent("xpath=//div[@class='token-input-dropdown-facebook']/ul[1]/li[1]"); // that dropdown menu
        $this->mouseDown("xpath=//div[@class='token-input-dropdown-facebook']/ul[1]/li[1]"); // use mouseDown instead of click

        // save it
        $this->click("_qf_Activity_upload-bottom");
        $this->waitForPageToLoad('30000');
        $this->assertTitle($keyword);
        $this->waitForElementPresent("contact-activity-selector-activity");
        $this->assertTrue($this->isTextPresent("Scheduled"),"Can not create the meeting ");

        // delete the meeting
        $this->waitForElementPresent("xpath=//td[@class='crm-contact-activity-links']"); // View | Edit | Delete  - block
        $this->click("xpath=//td[@class='crm-contact-activity-links']/span[1]/a[3]"); // DELETE
        $this->waitForPageToLoad('30000');
        $this->assertTitle($keyword);

        // confirm deletion
        $this->waitForElementPresent("_qf_Activity_next-bottom");
        $this->click("_qf_Activity_next-bottom");
        $this->waitForPageToLoad('30000');
        $this->assertTitle($keyword);
        $this->waitForElementPresent("contact-activity-selector-activity");

        $this->assertTrue($this->isTextPresent("No matches found."),"Can not delete the meeting ");
    }

    private function openAdvancedSearch() {
        // ADVANCED SEARCH actually is not a link
        // AND its content loads dynamically
        $this->click('class=civi-advanced-search-link');
        $this->waitForElementPresent('_qf_Advanced_refresh');
    }

    private function searchAndOpen($keyword) {
        $this->type('sort_name',$keyword);
        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('30000');
        $this->assertTitle('Advanced Search');
        $this->assertTrue(!$this->isTextPresent("No matches found"),"Advanced Search: Contact is not found in the database ");

        // click on the first result
        $this->click("xpath=//table[@class='selector crm-row-highlighter-processed']/tbody[1]/tr[1]/td[3]/a"); 
        $this->waitForPageToLoad('30000');
        $this->assertTitle("$keyword"); // check that right page is open
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