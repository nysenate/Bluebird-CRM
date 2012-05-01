<?php /* 
    Feb 27, 2012
    This test script uses the Advanced Search
    /Issue_code Aging
    /Keyword testkeyword
    /Positions S100-2011 (DIAZ)

    1. open sd99
    2. log in
    3. open advanced search
    4. run search by 
    /Issue_code Aging
    /Keyword testkeyword
    /Positions S100-2011 (DIAZ)
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
        $this->performTasks2();
    }

    private function openAdvancedSearch() {
        // ADVANCED SEARCH actually is not a link
        // its content loads dynamically
        $this->click('class=civi-advanced-search-link');
        $this->waitForElementPresent('_qf_Advanced_refresh');
    }

    private function performTasks2() {
        // select issue code Aging
        $this->click('crmasmSelect2');
        $this->select('crmasmSelect2',  "value=5"); //5 is Aging in html source code of the include
        $this->waitForElementPresent("xpath=//[@id='crmasmContainer2']/ul[1]/li[1]");
        $this->assertTrue($this->isTextPresent("Aging"), "Can not choose the Issue Code(s)");
        
        $this->click('token-input-contact_taglist_296');
        $this->waitForElementPresent('class=token-input-dropdown-facebook'); // dropdown element Begin typing a tag name
        //$this->waitForElementPresent('class=token-input-input-token-facebook'); // dropdown element Begin typing a tag name

        $keyword = "test";
        $this->typeKeys('token-input-contact_taglist_296',$keyword); // type the keyword
        $this->waitForElementPresent("xpath=//div[@class='token-input-dropdown-facebook']/ul[1]/li[1]"); // that dropdown menu
        $this->mouseDown("xpath=//div[@class='token-input-dropdown-facebook']/ul[1]/li[1]"); // use mouseDown instead of click

        $this->click('token-input-contact_taglist_292');
        $this->waitForElementPresent('class=token-input-dropdown-facebook'); // dropdown element Begin typing a tag name

        $keyword = "S100-2011 (DIAZ)";
        $this->typeKeys('token-input-contact_taglist_292',$keyword); // type the position
        $this->waitForElementPresent("xpath=//div[@class='token-input-dropdown-facebook']/ul[1]/li[1]"); // that dropdown menu
        $this->mouseDown("xpath=//div[@class='token-input-dropdown-facebook']/ul[1]/li[1]"); // use mouseDown instead of click

        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('30000');

        $this->assertTitle('Advanced Search');
        $this->assertTrue($this->isTextPresent("Print"),"Advanced Search: Contacts not found in the database ");
    }

    private function stop() {
        $this->waitForElementPresent('NonExistentElement');
    }

  // function to fill auto complete
  function fillAutoComplete( $text, $elementId ) {
      
      $this->typeKeys("$elementId", "$text");
      $this->waitForElementPresent("css=div.ac_results li");
      $this->click("css=div.ac_results li");
      $this->assertContains( $text, $this->getValue("$elementId"), 
                             "autocomplete expected $text but didn’t find it in " . $this->getValue("$elementId"));
      
  }


}
?>