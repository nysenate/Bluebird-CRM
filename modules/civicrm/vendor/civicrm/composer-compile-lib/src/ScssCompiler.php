<?php
namespace CCL;

class ScssCompiler extends \ScssPhp\ScssPhp\Compiler {

  protected $importPrefixes = [];

  /**
   * Return the file path for an import url if it exists
   *
   * @api
   *
   * @param string $url
   *
   * @return string|null
   */
  public function findImport($url, $currentDir = NULL) {
    $hasExtension = preg_match('/[.]s?css$/', $url);
    $pickFile = function($path) use ($hasExtension) {
      $dir = dirname($path);
      $file = basename($path);
      if ($hasExtension && file_exists("$dir/_$file")) {
        return "$dir/_$file";
      }
      if ($hasExtension && file_exists("$dir/$file")) {
        return "$dir/$file";
      }
      if (!$hasExtension && file_exists("$dir/_$file.scss")) {
        return "$dir/_$file.scss";
      }
      if (!$hasExtension && file_exists("$dir/$file.scss")) {
        return "$dir/$file.scss";
      }
      return NULL;
    };

    foreach ($this->importPrefixes as $prefixRegExp => $path) {
      if (preg_match($prefixRegExp, $url)) {
        if ($path = $pickFile(preg_replace($prefixRegExp, $path, $url))) {
          return $path;
        }
      }
    }

    return parent::findImport($url, $currentDir);
  }

  public function addImportPrefix(string $prefix, string $path) {
    $this->importPrefixes[';^' . preg_quote($prefix, ';') . ';'] = $path;
    uasort($this->importPrefixes, function($a, $b) {
      return strlen($b) - strlen($a);
    });
  }

}
