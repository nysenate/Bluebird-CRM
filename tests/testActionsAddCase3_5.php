<?php 
/*
    Mar 5, 2012
    This test script uses the Advanced Search
    Find the contact by name

    1. open sd99
    2. log in
    3. open advanced search
    4. run search by name
    5. open first found contact 
    6. Actions / Add case
    7. Medium in person
    8. Details Big case
    9. Place Albany Office
    10. Activity Budget Position
    11. Subject Budget case
    12. Case type General Complaint
    13. Save check and delete the case

    *** check EVERY STEP!

    *** Individual MUST HAVE NO CASES!
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

        $keyword = getSearchName();  // Config.php
        $this->searchAndOpen($keyword);

        // find Actions and click on it
        $this->waitForElementPresent("xpath=//div[@id='crm-contact-actions-link']");
        $this->click("xpath=//div[@id='crm-contact-actions-link']");

        // menu opens
        // find Meeting and click on it
        $this->click("link=Add Case");
        $this->waitForPageToLoad('30000');
        $this->assertTitle("Open Case");
        $this->waitForElementPresent("_qf_Case_upload-bottom");

        $this->select("medium_id","value=1");

        // type the name of the case
        $this->click("cke_28_label");
        $this->type("xpath=//textarea[@class='cke_source cke_enable_context_menu']","Big case");
        $this->click("cke_28_label");

        $this->select("custom_43_-1","value=albany_office");
        $this->select("custom_44_-1","value=budget_position");
        $this->type("activity_subject","Budget case");

        // save it
        $this->click("_qf_Case_upload-bottom");
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent("_qf_CaseView_cancel-bottom");
        $this->assertTrue($this->isTextPresent("Budget case"),"Can not create the case ");

        $this->click("_qf_CaseView_cancel-bottom"); // DONE button
        $this->waitForPageToLoad('30000');

        // now delete the case

        $this->waitForElementPresent("xpath=//table[@class='caseSelector']");
        $this->click("link=Delete");
        $this->waitForPageToLoad('30000');

        $this->waitForElementPresent("_qf_Case_next-bottom");
        $this->click("_qf_Case_next-bottom");
        $this->waitForPageToLoad('30000');

        $this->waitForElementPresent("Cases");
        $this->assertTrue(!$this->isTextPresent("Budget case"),"Couldn't delete the case. ");
 
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