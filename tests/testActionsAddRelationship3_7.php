<?php /* DONT REMOVE THE NEXT LINE. IT CONTAINS SETTINGS
SpouseName

    Mar 7, 2012
    This test script uses the Advanced Search
    Find the contact with name Ascher

    1. open sd99
    2. log in
    3. open advanced search
    4. run search by name = Ascher
    5. open first found contact 
    6. Actions / Add Relationship
    7. Spouse of / Mike Gordo
    8. Save relationship, check and delete the relationship

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
        $this->searchName = getSSName(0);
        $this->spouseName = getSSName(1);

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
        $keyword = $this->searchName;
        $this->searchAndOpen($keyword);

        // find Actions and click on it
        $this->waitForElementPresent("xpath=//div[@id='crm-contact-actions-link']");
        $this->click("xpath=//div[@id='crm-contact-actions-link']");

        // menu opens
        // find Meeting and click on it
        $this->click("link=Add Relationship");
        $this->waitForPageToLoad('30000');
        $this->assertTitle($keyword);
        $this->waitForElementPresent("search-button");

        $this->select("relationship_type_id","value=2_a_b"); // SPOUSE OF
        $this->type("rel_contact",$this->spouseName); // type the SPOUSE NAME
        $this->click("search-button"); // click search
        
        $this->waitForElementPresent("_qf_Relationship_upload-top");

        // choose the first person from the list
        $this->waitForElementPresent("xpath=//table[@id='option51']/tbody[1]/tr[1]/td[1]/input[1]");
        $this->click("xpath=//table[@id='option51']/tbody[1]/tr[1]/td[1]/input[1]");
        $this->waitForElementPresent("count_selected");

        $this->click("_qf_Relationship_upload-top"); // save the relationship
        $this->waitForPageToLoad('30000');
        $this->assertTitle($keyword);

        $this->waitForElementPresent("option11"); // wait for page to load
        $this->assertTrue($this->isTextPresent("Spouse of"),"Can not save the relationship ");
                
        // now delete the relationship
        $this->click("xpath=//table[@id='option11']/tbody[1]/tr[1]/td[8]/span[2]");
        $this->waitForElementPresent("xpath=//table[@id='option11']/tbody[1]/tr[1]/td[8]/span[2]/ul[1]/li[2]/a[1]");
        $this->click("xpath=//table[@id='option11']/tbody[1]/tr[1]/td[8]/span[2]/ul[1]/li[2]/a[1]"); // "delete"
        
        $this->waitForPageToLoad('30000');
        $this->assertTitle($keyword);
        $this->assertTrue(!$this->isTextPresent("Spouse of"),"Can not delete the relationship ");


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
        $this->assertTrue($this->isTextPresent("Select Records:"),"Advanced Search: Contact is not found in the database ");

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