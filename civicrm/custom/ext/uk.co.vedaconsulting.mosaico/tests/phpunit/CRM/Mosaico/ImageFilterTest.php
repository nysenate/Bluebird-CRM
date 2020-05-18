<?php

use Civi\Test\EndToEndInterface;

require_once __DIR__ . '/TestCase.php';

/**
 * Class CRM_Mosaico_UrlFilterTest
 *
 * Unit tests for filtering of URLs within HTML.
 *
 * @group e2e
 */
class CRM_Mosaico_ImageFilterTest extends CRM_Mosaico_TestCase implements EndToEndInterface {

  public function filterExamples() {
    $dmasterConfig = [
      'BASE_URL' => 'http://dmaster.bknix:8001/sites/default/files/civicrm/persist/contribute/',
      'BASE_DIR' => '/home/foobar/bknix/build/dmaster/sites/default/files/civicrm/persist/contribute/',
      'UPLOADS_URL' => 'images/uploads/',
      'UPLOADS_DIR' => 'images/uploads/',
      'STATIC_URL' => 'images/uploads/static/',
      'STATIC_DIR' => 'images/uploads/static/',
      // 'THUMBNAILS_URL' => 'images/uploads/thumbnails/',
      // 'THUMBNAILS_DIR' => 'images/uploads/thumbnails/',
      // 'THUMBNAIL_WIDTH' => 90,
      // 'THUMBNAIL_HEIGHT' => 90,
      // 'MOBILE_MIN_WIDTH' => 246,
    ];

    $htmlTpl = '<p>Hello <img src="%s"> world.</p>';

    $cases = [];

    $cases[] = [
      $dmasterConfig,
      sprintf($htmlTpl, htmlentities('http://dmaster.bknix:8001/civicrm/mosaico/img?src=http%3A%2F%2Fdmaster.bknix%3A8001%2Fsites%2Fdefault%2Ffiles%2Fcivicrm%2Fpersist%2Fcontribute%2Fimages%2Fuploads%2FFileUploadBehavior_e76563d740531818a168bc5bd099b898.png&method=resize&params=534%2Cnull')),
      sprintf($htmlTpl, htmlentities('http://dmaster.bknix:8001/sites/default/files/civicrm/persist/contribute/images/uploads/static/FileUploadBehavior_e76563d740531818a168bc5bd099b898.png')),
    ];

    return $cases;
  }

  /**
   * @param string $inputHtml
   * @param string $expectHtml
   * @dataProvider filterExamples
   */
  public function testFilter($config, $inputHtml, $expectHtml) {
    $filter = new CRM_Mosaico_ImageFilter($config);
    $e = \Civi\Core\Event\GenericHookEvent::create([
      'content' => [
        'subject' => 'ignore',
        'text' => 'ignore',
        'html' => $inputHtml,
      ]
    ]);
    $filter->alterMailContent($e);
    $this->assertEquals('ignore', $e->content['subject']);
    $this->assertEquals('ignore', $e->content['text']);
    $this->assertEquals($expectHtml, $e->content['html']);
  }

}
