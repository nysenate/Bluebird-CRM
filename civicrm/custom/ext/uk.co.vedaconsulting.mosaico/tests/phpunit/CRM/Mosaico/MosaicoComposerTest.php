<?php

use Civi\Test\EndToEndInterface;

require_once __DIR__ . '/TestCase.php';

/**
 * Class CRM_Mosaico_MosaicoComposerTest
 *
 * General integration test for special composition behavior of
 * Mosaico-based mailings.
 *
 * @group e2e
 */
class CRM_Mosaico_MosaicoComposerTest extends CRM_Mosaico_TestCase implements EndToEndInterface {

  public function getComposerExamples() {
    $cases = []; // array($field, $inputValue, $expectRegex).

    $cases[] = [
      'body_html',
      '<p>Hello <img noise="1" src="foo.png"> world.</p>',
      ';noise="1" src="http://[^"]+/foo.png";',
    ];
    $cases[] = [
      'body_text',
      'Go to [show_link]',
      ';Go to http://[^"]+civicrm/mailing/view;',
    ];
    $cases[] = [
      'body_text',
      'Go to [unsubscribe_link]',
      ';Go to http://[^"]+civicrm/mailing/unsubscribe;',
    ];

    return $cases;
  }

  /**
   * Generate a preview of an outgoing email. Check that the field is
   * rendered the expected way.
   *
   * @param string $field
   *   Ex: 'body_html', 'body_text', 'subject'.
   * @param string $inputValue
   *   Ex: 'Hello {contact.first_name}'.
   * @param string $expectRegex
   *   Ex: ';Hello .+;'
   *
   * @dataProvider getComposerExamples
   */
  public function testMailingPreview($field, $inputValue, $expectRegex) {
    $this->assertEquals('installed', CRM_Extension_System::singleton()->getManager()->getStatus('org.civicrm.flexmailer'));

    $contactId = $this->getContactId($GLOBALS['_CV']['ADMIN_USER']);

    $params = [
      'template_type' => 'mosaico',
      'template_options' => ['nonce' => 1],
      'subject' => 'Placeholder',
      'body_text' => "Placeholder",
      'body_html' => "Placeholder",
      'name' => 'MosaicoComposerTest' . md5(uniqid()),
      'created_id' => $contactId,
      'header_id' => '',
      'footer_id' => '',
      'api.Mailing.preview' => [
        'id' => '$value.id',
        'contact_id' => $contactId,
      ],
      'options' => [
        'force_rollback' => 1,
      ],
    ];
    $params[$field] = $inputValue;

    $result = $this->callAPISuccess('mailing', 'create', $params);
    $previewResult = $result['values'][$result['id']]['api.Mailing.preview'];

    $this->assertRegExp($expectRegex, $previewResult['values'][$field]);
  }

  protected function getContactId($username) {
    $config = CRM_Core_Config::singleton();

    $ufID = $config->userSystem->getUfId($username);
    if (!$ufID) {
      throw new \RuntimeException("Failed to find user ID: \"$username\"");
    }

    $contactID = CRM_Core_BAO_UFMatch::getContactId($ufID);
    if (!$contactID) {
      throw new \RuntimeException("Failed to find contact ID: \"$username\" / $ufID");
    }

    return $contactID;
  }

}
