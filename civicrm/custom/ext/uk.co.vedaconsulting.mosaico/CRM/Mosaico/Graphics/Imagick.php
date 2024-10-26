<?php

/**
 * Class CRM_Mosaico_Graphics_Imagick
 *
 * A graphics provider which directly uses the imagick API.
 *
 * This is deprecated because we've had several random reports wherein
 * imagick operations fail and we haven't been able to determine why.
 *
 * @see https://github.com/voidlabs/mosaico/blob/master/backend/README.txt
 */
class CRM_Mosaico_Graphics_Imagick extends CRM_Mosaico_Graphics_Interface {

  /**
   * CRM_Mosaico_Graphics_Imagick constructor.
   */
  public function __construct() {
    if (!extension_loaded('imagick') || !class_exists("Imagick")) {
      throw new CRM_Mosaico_Graphics_Exception("Failed to locate PHP-ImageMagick extension.");
    }
  }

  public function sendPlaceholder($width, $height) {
    $image = new Imagick();

    $image->newImage($width, $height, "#707070");
    $image->setImageFormat("png");

    $x = 0;
    $y = 0;
    $size = 40;

    $draw = new ImagickDraw();

    while ($y < $height) {
      $draw->setFillColor("#808080");

      $points = [
        ["x" => $x, "y" => $y],
        ["x" => $x + $size, "y" => $y],
        ["x" => $x + $size * 2, "y" => $y + $size],
        ["x" => $x + $size * 2, "y" => $y + $size * 2],
      ];

      $draw->polygon($points);

      $points = [
        ["x" => $x, "y" => $y + $size],
        ["x" => $x + $size, "y" => $y + $size * 2],
        ["x" => $x, "y" => $y + $size * 2],
      ];

      $draw->polygon($points);

      $x += $size * 2;

      if ($x > $width) {
        $x = 0;
        $y += $size * 2;
      }
    }

    $draw->setFillColor("#B0B0B0");
    $draw->setFontSize($width / 5);
    $draw->setFontWeight(800);
    $draw->setGravity(Imagick::GRAVITY_CENTER);
    $draw->annotation(0, 0, $width . " x " . $height);

    $image->drawImage($draw);

    header("Content-type: image/png");

    echo $image;
  }

  public function createResizedImage($srcFile, $destFile, $width, $height) {
    $config = CRM_Mosaico_Utils::getConfig();
    $mobileMinWidth = $config['MOBILE_MIN_WIDTH'];

    $image = new Imagick($srcFile);
    $this->adjustResizeDimensions($image->getImageWidth(), $image->getImageHeight(), $width, $height);

    $resize_width = $width;
    $resize_height = $image->getImageHeight();
    if ($width < $mobileMinWidth) {
      // DS: resize images to higher resolution, for images with lower width than needed for mobile devices
      // DS: FIXME: only works for 'resize' method, not 'cover' methods.
      // Partially resolves - https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/issues/50
      $fraction = ceil($mobileMinWidth / $width);
      $resize_width = $resize_width * $fraction;
      $resize_height = $resize_height * $fraction;
    }
    // We get 0 for height variable from mosaico
    // In order to use last parameter(best fit), this will make right scale, as true in 'resizeImage' menthod, we can't have 0 for height
    // hence retreiving height from image
    // more details about best fit http://php.net/manual/en/imagick.resizeimage.php
    $image->resizeImage($resize_width, $resize_height, Imagick::FILTER_LANCZOS, 1.0, TRUE);

    //save image for next time so don't need to resize each time
    if ($f = fopen($destFile, "w")) {
      $image->writeImageFile($f);
    }
    else {
      throw new \Exception("Failed to write $destFile");
    }
  }

  public function createCoveredImage($srcFile, $destFile, $width, $height) {
    $image = new Imagick($srcFile);

    $image_geometry = $image->getImageGeometry();
    $this->adjustResizeDimensions($image_geometry["width"], $image_geometry["height"], $width, $height);

    $width_ratio = $image_geometry["width"] / $width;
    $height_ratio = $image_geometry["height"] / $height;

    $resize_width = $width;
    $resize_height = $height;

    if ($width_ratio > $height_ratio) {
      $resize_width = 0;
    }
    else {
      $resize_height = 0;
    }

    $image->resizeImage($resize_width, $resize_height,
      Imagick::FILTER_LANCZOS, 1.0);

    $image_geometry = $image->getImageGeometry();

    $x = ($image_geometry["width"] - $width) / 2;
    $y = ($image_geometry["height"] - $height) / 2;

    $image->cropImage($width, $height, $x, $y);

    //save image for next time so don't need to resize each time
    if ($f = fopen($destFile, "w")) {
      $image->writeImageFile($f);
    }
    else {
      throw new \Exception("Failed to write $destFile");
    }
  }

}
