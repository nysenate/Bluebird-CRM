<?php /* 

    Feb 27, 2012
    This test script is trying to find Smith using the Find Contact
    And then using the Find Anything

    1. open sd99
    2. log in
    3. try to find Smith using Find Contacts
    4. check if found something
    5. try to find Smith using Find Anything
    6. check if found something

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

        // use FIND CONTACTS
        $searchName = "Smith";
        $this->type('civi_sort_name', $searchName);
        $this->click('find_contacts');
        $this->waitForPageToLoad('30000');        
        $this->assertTitle('Find Contacts');
        $this->assertTrue($this->isTextPresent("$searchName,"), "Find Contacts: Contact \"$searchName\" not found in the database ");

        // use FIND ANYTHING
        $searchName = "Smith";
        $this->type('civi_text_search', $searchName);
        $this->click('name=_qf_Custom_refresh');
        $this->waitForPageToLoad('30000');        
        $this->assertTitle('Find Anything');
        $this->assertTrue($this->isTextPresent("$searchName,"), "Find Anything: Contact \"$searchName\" not found in the database ");

        //check if some form field called Basic contains this text 'alpha-filter'
        //$this->assertContains('alpha-filter', $this->getValue('Basic'), "$searchName is not found!");
    }

}
?>