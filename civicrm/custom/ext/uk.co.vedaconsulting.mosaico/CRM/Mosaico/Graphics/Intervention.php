<?php

use CRM_Mosaico_ExtensionUtil as E;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Class CRM_Mosaico_Graphics_Intervention
 *
 * @see https://github.com/voidlabs/mosaico/blob/master/backend/README.txt
 * @see http://image.intervention.io/getting_started/introduction
 */
class CRM_Mosaico_Graphics_Intervention extends CRM_Mosaico_Graphics_Interface {

  const FONT_PATH = 'packages/mosaico/dist/vendor/notoregular/NotoSans-Regular-webfont.ttf';

  /**
   * CRM_Mosaico_Graphics_Intervention constructor.
   *
   * @param array $options
   * @see Image::configure()
   * @throws CRM_Mosaico_Graphics_Exception
   */
  public function __construct($options) {
    if (!self::isClassLoaded()) {
      throw new CRM_Mosaico_Graphics_Exception("Failed to locate classes for \"intervention/image\" API. Ensure that you have downloaded all dependencies.");
    }
    Image::configure($options);
  }

  /**
   * @return bool
   */
  public static function isClassLoaded() {
    return class_exists('Intervention\Image\ImageManagerStatic');
  }

  public function sendPlaceholder($width, $height) {
    $img = Image::canvas($width, $height, '#707070');

    $x = 0;
    $y = 0;
    $size = 40;
    while ($y < $height) {
      $points = [
        ["x" => $x, "y" => $y],
        ["x" => $x + $size, "y" => $y],
        ["x" => $x + $size * 2, "y" => $y + $size],
        ["x" => $x + $size * 2, "y" => $y + $size * 2],
      ];
      $img->polygon(self::flattenPoints($points), function ($draw) {
        $draw->background("#808080");
      });
      $points = [
        ["x" => $x, "y" => $y + $size],
        ["x" => $x + $size, "y" => $y + $size * 2],
        ["x" => $x, "y" => $y + $size * 2],
      ];
      $img->polygon(self::flattenPoints($points), function ($draw) {
        $draw->background("#808080");
      });
      $x += $size * 2;
      if ($x > $width) {
        $x = 0;
        $y += $size * 2;
      }
    }
    $img->text("{$width} x {$height}", $width / 2, $height / 2, function ($font) use ($width) {
      $font->file(E::path(self::FONT_PATH));
      $font->size($width / 5);
      $font->align('center');
      $font->valign('middle');
      $font->color("#B0B0B0");
    });

    // $img->response returns a \Symfony\Component\HttpFoundation\Response object which will call __toString unless we pass in the send() method in Drupal8.
    $response = $img->response('png');
    if (is_object($response)) {
      echo $response->send();
    }
    else {
      echo $response;
    }
  }

  /**
   * @param array $points
   *   List of points; each is an array of "x","y" values.
   *   Ex: $points[0]=['x'=>'100', 'y'=>'200'];
   *   Ex: $points[1]=['x'=>'300', 'y'=>'400'];
   * @return array
   *   List of arrays. All "x","y" values indicated positoinally.
   *   Ex: ['100','200','300','400'].
   */
  protected static function flattenPoints($points) {
    $r = [];
    foreach ($points as $point) {
      $r[] = $point['x'];
      $r[] = $point['y'];
    }
    return $r;
  }

  public function createResizedImage($srcFile, $destFile, $width, $height) {
    $config = CRM_Mosaico_Utils::getConfig();
    $img = Image::make($srcFile);
    $this->adjustResizeDimensions($img->width(), $img->height(), $width, $height);

    if ($width && $height) {
      $img->resize($width, $height);
    }
    elseif ($width && !$height) {
      $mobileMinWidth = $config['MOBILE_MIN_WIDTH'];
      $img->widen(max($width, $mobileMinWidth));
    }
    elseif (!$width && $height) {
      $mobileMinHeight = ceil($img->height() * $config['MOBILE_MIN_WIDTH'] / $img->width());
      $img->heighten(max($height, $mobileMinHeight));
    }

    $img->save($destFile);
  }

  public function createCoveredImage($srcFile, $destFile, $width, $height) {
    $img = Image::make($srcFile);
    $this->adjustResizeDimensions($img->width(), $img->height(), $width, $height);

    $ratios = [];
    if ($width) {
      $ratios[] = $width / $img->width();
    }
    if ($height) {
      $ratios[] = $height / $img->height();
    }
    if (!$width && !$height) {
      throw new \Exception("Must specify a width and/or height");
    }

    $scaledRatio = max($ratios);
    $scaledBox = [
      'w' => round($img->width() * $scaledRatio),
      'h' => round($img->height() * $scaledRatio),
    ];
    $tgtBox = [
      'w' => $width ?: round($img->width() * $height / $img->height()),
      'h' => $height ?: round($img->height() * $width / $img->width()),
    ];

    $img->resize($scaledBox['w'], $scaledBox['h']);

    // Make tgtBox from the center of the scaledBox.
    $img->crop(
      $tgtBox['w'],
      $tgtBox['h'],
      round(($scaledBox['w'] - $tgtBox['w']) / 2),
      round(($scaledBox['h'] - $tgtBox['h']) / 2)
    );

    $img->save($destFile);
  }

}
