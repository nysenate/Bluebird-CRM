<?php

/**
 * Interface CRM_Mosaico_Graphics_Interface
 *
 * This is an abstraction to help us among a couple graphics backends.
 *
 * NOTE: This interface has not been publicly documented because it's not
 * been thought through very aggressively, and the contract could
 * probably be better. As long as it remains internal, we have some
 * flexibility to clean it up.
 */
abstract class CRM_Mosaico_Graphics_Interface {

  /**
   * Generate a placeholder image.
   *
   * @param int $width
   * @param int $height
   * @return mixed
   */
  abstract public function sendPlaceholder($width, $height);

  /**
   * Generate a scaled version of the image.
   *
   * "resize" can receive one dimension to resize while keeping the A/R, or 2 to resize the image to be inside the dimensions.
   *
   * @see https://github.com/voidlabs/mosaico/blob/master/backend/README.txt
   *
   * @param string $src
   *   Local file path.
   * @param string $dest
   *   Local file path.
   * @param int|NULL $width
   *   Width in pixels.
   *   NOTE: NULL or 0 are interpreted "auto-scaled".
   * @param int|NULL $height
   *   Height in pixels.
   *   NOTE: NULL or 0 are interpreted "auto-scaled".
   * @return mixed
   */
  abstract public function createResizedImage($src, $dest, $width, $height);

  /**
   * Generate a "cover" version of the image.
   *
   * "cover" will resize the image keeping the aspect ratio and covering the whole dimension (cutting it if different A/R)
   *
   * @see https://github.com/voidlabs/mosaico/blob/master/backend/README.txt
   *
   * @param string $src
   *   Local file path.
   * @param string $dest
   *   Local file path.
   * @param int|NULL $width
   *   Width in pixels.
   *   NOTE: NULL or 0 are interpreted "auto-scaled".
   * @param int|NULL $height
   *   Height in pixels.
   *   NOTE: NULL or 0 are interpreted "auto-scaled".
   * @return mixed
   */
  abstract public function createCoveredImage($src, $dest, $width, $height);

  /**
   * Adjust resize dimensions in order to preserve the best possible resolution for the image.
   *
   * @param int $imgWidth
   *   Image width in pixels.
   * @param int $imgHeight
   *   Image height in pixels.
   * @param int|NULL $resizeWidth
   *   Resize width in pixels.
   * @param int|NULL $resizeHeight
   *   Resize height in pixels.
   * @return float|null
   */
  public function adjustResizeDimensions($imgWidth, $imgHeight, &$resizeWidth, &$resizeHeight) {
    $scaleFactor = NULL;
    $scales[Civi::settings()->get('mosaico_scale_width_limit1')] = Civi::settings()->get('mosaico_scale_factor1');
    $scales[Civi::settings()->get('mosaico_scale_width_limit2')] = Civi::settings()->get('mosaico_scale_factor2');
    $scales = array_filter($scales);
    ksort($scales, SORT_NUMERIC);
    if (!empty($scales) && $resizeWidth) {
      foreach ($scales as $width => $slevel) {
        if ($resizeWidth <= $width) {
          $scaleFactor = $slevel;
          break;
        }
      }
    }
    if (empty($scaleFactor)) {
      return NULL;
    }
    // If scale-factor make new width bigger than that of image itself, re-compute scale-factor to
    // maximum possible.
    if ($scaleFactor && $resizeWidth && $imgWidth && ($imgWidth < ($resizeWidth * $scaleFactor))) {
      $possibleLevels[] = $imgWidth / $resizeWidth;
    }
    if ($scaleFactor && $resizeHeight && $imgHeight && ($imgHeight < ($resizeHeight * $scaleFactor))) {
      $possibleLevels[] = $imgHeight / $resizeHeight;
    }
    if (!empty($possibleLevels)) {
      $scaleFactor = max($possibleLevels);
    }
    if ($scaleFactor && $resizeWidth) {
      $resizeWidth = round($resizeWidth * $scaleFactor);
    }
    if ($scaleFactor && $resizeHeight) {
      $resizeHeight = round($resizeHeight * $scaleFactor);
    }
    return $scaleFactor;
  }

}
