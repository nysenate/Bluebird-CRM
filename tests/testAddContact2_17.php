<?php

/*
    Feb 17, 2012
    This test script adds the Individual contact to the database

    1. opens sd99
    2. logs in
    3. adds individual contact
    4. displays the created contact

*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'BluebirdSeleniumSettings.php';
require_once 'SampleGenerator.php';


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
        
        /* 
            for pause uncomment the following line
        */
        $this->setSleep($this->settings->sleepTime);
    }
 
    public function testTitle()
    {
        $this->openAndWait('http://sd99/');
        $this->assertTitle('Bluebird');         // make sure Bluebird is open
        $this->webtestLogin();
        $this->performTasks();

        $this->waitForPageToLoad('30000');
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
        $this->openCreateNewIndividual();

        $fname = getFirstName();
        $lname = getLastName();
        $email = getEmail($fname, $lname, '@nomatter.net');

        $this->webtestAddContact($fname, $lname, $email);
    }

    private function openCreateNewIndividual() {
        $this->click('create-link');
        $this->waitForElementPresent('link=New Individual');
        $this->click('link=New Individual');
        $this->waitForPageToLoad('30000');
    }

    public function webtestAddContact( $fname = 'Anthony', $lname = 'Anderson', $email = null ) {
        $this->waitForElementPresent('_qf_Contact_upload_view-bottom');
        $this->type('first_name', $fname);
        $this->type('last_name', $lname);
        $this->type('email_1_email', $email);
        $this->type('address_1_location_type_id', 'Home');
        
        // street address
        $this->type('address_1_street_address', getStreetAddress());

        // city & zip code
        $this->type('address_1_city', getStreetAddress_City());
        $this->type('address_1_postal_code', getStreetAddress_Zip());

        // SAVE DATA
        $this->click('_qf_Contact_upload_view-bottom');
        $this->waitForPageToLoad('30000');        
    }


}
?>