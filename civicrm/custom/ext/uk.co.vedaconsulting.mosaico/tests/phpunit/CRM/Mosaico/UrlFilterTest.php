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
class CRM_Mosaico_UrlFilterTest extends CRM_Mosaico_TestCase implements EndToEndInterface {

  public function filterExamples() {
    $cases = array();

    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <img src="http://other.example.com/foo.png"> world.</p>',
      '<p>Hello <img src="http://other.example.com/foo.png"> world.</p>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <img noise="1" src="foo.png"> world.</p>',
      '<p>Hello <img noise="1" src="http://abc.example.com/def/foo.png"> world.</p>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      "<p>Hello <img noise=\"1\"\n src=\"foo.png\"> world.</p>",
      "<p>Hello <img noise=\"1\"\n src=\"http://abc.example.com/def/foo.png\"> world.</p>",
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <IMG noise="1" SRC="foo.png"> world.</p>',
      '<p>Hello <IMG noise="1" SRC="http://abc.example.com/def/foo.png"> world.</p>',
    );
    $cases[] = array(
      'http://abc.example.com/ghi/',
      '<img src=\'bar.png\' noise="2"><p>Hello <img noise="1" src="foo.png"> world.</p>',
      '<img src=\'http://abc.example.com/ghi/bar.png\' noise="2"><p>Hello <img noise="1" src="http://abc.example.com/ghi/foo.png"> world.</p>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <img noise="1" src="foo.png"/> world.</p>',
      '<p>Hello <img noise="1" src="http://abc.example.com/def/foo.png"/> world.</p>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<garbage><p>Hello <img noise="1" src="foo.png"> world.</p>',
      '<garbage><p>Hello <img noise="1" src="http://abc.example.com/def/foo.png"> world.</p>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <img noise="1" src="foo.png"> world.</p></garbage>',
      '<p>Hello <img noise="1" src="http://abc.example.com/def/foo.png"> world.</p></garbage>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <garbage src="foo.png"> world.</p></garbage>',
      '<p>Hello <garbage src="foo.png"> world.</p></garbage>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<table border="0" background="example.gif"><tbody>...',
      '<table border="0" background="http://abc.example.com/def/example.gif"><tbody>...',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <img src="/foo.png"> world.</p>',
      '<p>Hello <img src="http://abc.example.com/foo.png"> world.</p>',
    );
    $cases[] = array(
      'http://abc.example.com/def/',
      '<p>Hello <img src=""> world.</p>',
      '<p>Hello <img src=""> world.</p>',
    );
    return $cases;
  }

  /**
   * @param string $inputHtml
   * @param string $expectHtml
   * @dataProvider filterExamples
   */
  public function testFilterArray($baseUrl, $inputHtml, $expectHtml) {
    $filter = new CRM_Mosaico_UrlFilter();
    $filter->setBaseUrl($baseUrl);
    list($actual) = $filter->filterHtml(array($inputHtml));
    $this->assertEquals($expectHtml, $actual);
  }

  /**
   * @param string $inputHtml
   * @param string $expectHtml
   * @dataProvider filterExamples
   */
  public function testFilterString($baseUrl, $inputHtml, $expectHtml) {
    $filter = new CRM_Mosaico_UrlFilter();
    $filter->setBaseUrl($baseUrl);
    $actual = $filter->filterHtml($inputHtml);
    $this->assertEquals($expectHtml, $actual);
  }

}
