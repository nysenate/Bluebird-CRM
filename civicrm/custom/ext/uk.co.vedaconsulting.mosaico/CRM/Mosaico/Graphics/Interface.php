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
interface CRM_Mosaico_Graphics_Interface {

  /**
   * Generate a placeholder image.
   *
   * @param int $width
   * @param int $height
   * @return mixed
   */
  public function sendPlaceholder($width, $height);

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
  public function createResizedImage($src, $dest, $width, $height);

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
  public function createCoveredImage($src, $dest, $width, $height);

}
