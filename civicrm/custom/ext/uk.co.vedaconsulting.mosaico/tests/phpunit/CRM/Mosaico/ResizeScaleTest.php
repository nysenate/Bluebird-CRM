<?php

use CRM_Mosaico_ExtensionUtil as E;
use Civi\Test\EndToEndInterface;

require_once __DIR__ . '/TestCase.php';

/**
 * Test resize dimension scaling method - adjustResizeDimensions for various sizes.
 *
 * @group e2e
 * @see cv
 */
class CRM_Mosaico_ResizeScaleTest extends CRM_Mosaico_TestCase implements EndToEndInterface {

  /**
   * Test adjustResizeDimensions method for various inputs.
   */
  public function testResizeScaleFactor() {
    // 3x - upto 190 pixels
    Civi::settings()->set('mosaico_scale_width_limit1', 190);
    Civi::settings()->set('mosaico_scale_factor1', 3);
    // 2x - not set
    Civi::settings()->set('mosaico_scale_width_limit2', '');
    Civi::settings()->set('mosaico_scale_factor2', '');

    $graphics = CRM_Mosaico_Services::createGraphics();

    // test resize for 166x90 - matches settings, for an image of size 1080x1080
    $resizeWidth  = 166;
    $resizeHeight = 90;
    $graphics->adjustResizeDimensions(1080, 1080, $resizeWidth, $resizeHeight);
    // dimensions should be 3x now
    $this->assertEquals(166 * 3, $resizeWidth);
    $this->assertEquals(90 * 3, $resizeHeight);

    // test resize for 258x100 - does not match settings
    $resizeWidth  = 258;
    $resizeHeight = 100;
    $graphics->adjustResizeDimensions(1080, 1080, $resizeWidth, $resizeHeight);
    // dimensions should remain same
    $this->assertEquals(258, $resizeWidth);
    $this->assertEquals(100, $resizeHeight);

    // 2x - add settings
    Civi::settings()->set('mosaico_scale_width_limit2', '285');
    Civi::settings()->set('mosaico_scale_factor2', '2');

    // test resize for 258x100 - matches settings
    $resizeWidth  = 258;
    $resizeHeight = 100;
    $graphics->adjustResizeDimensions(1080, 1080, $resizeWidth, $resizeHeight);
    // dimensions should be 2x now
    $this->assertEquals(258 * 2, $resizeWidth);
    $this->assertEquals(100 * 2, $resizeHeight);

    // test over scaling i.e scaling would result in more size than the image
    $resizeWidth  = 258;
    $resizeHeight = 100;
    $sf = $graphics->adjustResizeDimensions(358, 200, $resizeWidth, $resizeHeight);
    // dimensions should be almost same as that of image ignoring the scaling config, 
    // keeping the ratio of resize dimensions
    $this->assertEquals(round($sf * 258), $resizeWidth);
    $this->assertEquals(round($sf * 100), $resizeHeight);

    // test no scaling - configs not set
    Civi::settings()->set('mosaico_scale_width_limit1', '');
    Civi::settings()->set('mosaico_scale_factor1', '');
    Civi::settings()->set('mosaico_scale_width_limit2', '');
    Civi::settings()->set('mosaico_scale_factor2', '');
    $resizeWidth  = 258;
    $resizeHeight = 100;
    $graphics->adjustResizeDimensions(1080, 1080, $resizeWidth, $resizeHeight);
    // dimensions should remain same
    $this->assertEquals(258, $resizeWidth);
    $this->assertEquals(100, $resizeHeight);
  }

}
