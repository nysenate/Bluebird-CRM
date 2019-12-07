<?php
namespace Civi\FlexMailer;

/**
 * Class MailingPreviewTest
 *
 * @group headless
 */
class MailingPreviewTest extends \CiviUnitTestCase {

  protected $_groupID;
  protected $_email;
  protected $_apiversion = 3;
  protected $_params = array();
  protected $_entity = 'Mailing';
  protected $_contactID;

  /**
   * APIv3 result from creating an example footer
   * @var array
   */
  protected $footer;

  public function setUp() {
    // Activate before transactions are setup.
    $manager = \CRM_Extension_System::singleton()->getManager();
    if ($manager->getStatus('org.civicrm.flexmailer') !== \CRM_Extension_Manager::STATUS_INSTALLED) {
      $manager->install(array('org.civicrm.flexmailer'));
    }

    parent::setUp();

    \CRM_Core_BAO_Setting::setItem(TRUE, 'Mailing Preferences', 'experimentalFlexMailerEngine');

    $this->useTransaction();
    \CRM_Mailing_BAO_MailingJob::$mailsProcessed = 0; // DGW
    $this->_contactID = $this->individualCreate();
    $this->_groupID = $this->groupCreate();
    $this->_email = 'test@test.test';
    $this->_params = array(
      'subject' => 'Hello {contact.display_name}',
      'body_text' => "This is {contact.display_name}.\nhttps://civicrm.org\n{domain.address}{action.optOutUrl}",
      'body_html' => "<p>This is {contact.display_name}.</p><p><a href='https://civicrm.org/'>CiviCRM.org</a></p><p>{domain.address}{action.optOutUrl}</p>",
      'name' => 'mailing name',
      'created_id' => $this->_contactID,
      'header_id' => '',
      'footer_id' => '',
    );

    $this->footer = civicrm_api3('MailingComponent', 'create', array(
      'name' => 'test domain footer',
      'component_type' => 'footer',
      'body_html' => '<p>From {domain.address}. To opt out, go to {action.optOutUrl}.</p>',
      'body_text' => 'From {domain.address}. To opt out, go to {action.optOutUrl}.',
    ));
  }

  public function tearDown() {
    \CRM_Mailing_BAO_MailingJob::$mailsProcessed = 0; // DGW
    parent::tearDown();
  }

  public function testMailerPreview() {
    // BEGIN SAMPLE DATA
    $contactID = $this->individualCreate();
    $displayName = $this->callAPISuccess('contact', 'get',
      array('id' => $contactID));
    $displayName = $displayName['values'][$contactID]['display_name'];
    $this->assertTrue(!empty($displayName));

    $params = $this->_params;
    $params['api.Mailing.preview'] = array(
      'id' => '$value.id',
      'contact_id' => $contactID,
    );
    $params['options']['force_rollback'] = 1;
    // END SAMPLE DATA

    $maxIDs = array(
      'mailing' => \CRM_Core_DAO::singleValueQuery('SELECT MAX(id) FROM civicrm_mailing'),
      'job' => \CRM_Core_DAO::singleValueQuery('SELECT MAX(id) FROM civicrm_mailing_job'),
      'group' => \CRM_Core_DAO::singleValueQuery('SELECT MAX(id) FROM civicrm_mailing_group'),
      'recipient' => \CRM_Core_DAO::singleValueQuery('SELECT MAX(id) FROM civicrm_mailing_recipients'),
    );
    $result = $this->callAPISuccess('mailing', 'create', $params);
    $this->assertDBQuery($maxIDs['mailing'],
      'SELECT MAX(id) FROM civicrm_mailing'); // 'Preview should not create any mailing records'
    $this->assertDBQuery($maxIDs['job'],
      'SELECT MAX(id) FROM civicrm_mailing_job'); // 'Preview should not create any mailing_job record'
    $this->assertDBQuery($maxIDs['group'],
      'SELECT MAX(id) FROM civicrm_mailing_group'); // 'Preview should not create any mailing_group records'
    $this->assertDBQuery($maxIDs['recipient'],
      'SELECT MAX(id) FROM civicrm_mailing_recipients'); // 'Preview should not create any mailing_recipient records'

    $previewResult = $result['values'][$result['id']]['api.Mailing.preview'];
    $this->assertEquals("Hello $displayName",
      $previewResult['values']['subject']);
    $this->assertContains("This is $displayName",
      $previewResult['values']['body_text']);
    $this->assertContains("<p>This is $displayName.</p>",
      $previewResult['values']['body_html']);
    $this->assertEquals('flexmailer', $previewResult['values']['_rendered_by_']);
  }

}
