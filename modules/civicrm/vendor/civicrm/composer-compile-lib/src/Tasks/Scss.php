<?php
namespace CCL\Tasks;

use CCL\ScssCompiler;
use tubalmartin\CssMin\Minifier;

class Scss {

  /**
   * Compile some SCSS file(s).
   *
   * @param array $task
   *   With keys:
   *   - scss-imports: string[], list of paths with SCSS helper files
   *   - scss-import-prefixes: array, key-value mapping where keys are "logical file prefixes" and values are file-paths
   *   - scss-includes: string[], an alias for 'scss-imports'
   *   - scss-files: array, list of files to generate and their inputs
   *     Ex: ['generatedFile.css': 'sourceFile.scss']
   *
   * @link https://github.com/civicrm/composer-compile-plugin/blob/master/doc/tasks.md
   */
  public static function compile(array $task) {
    $scssCompiler = new ScssCompiler();
    $includes = $task['scss-imports'] ?? $task['scss-includes'] ?? [];
    foreach ($includes as $include) {
      $scssCompiler->addImportPath($include);
    }
    $prefixes = $task['scss-import-prefixes'] ?? [];
    foreach ($prefixes as $prefix => $path) {
      $scssCompiler->addImportPrefix($prefix, $path);
    }

    $minifier = new Minifier();

    if (empty($task['scss-files'])) {
      throw new \InvalidArgumentException("Invalid task: required argument 'scss-files' is missing");
    }
    foreach ($task['scss-files'] as $outputFile => $inputFile) {
      if (!file_exists($inputFile)) {
        throw new \InvalidArgumentException("File does not exist: " . $inputFile);
      }
      $inputScss = file_get_contents($inputFile);
      $css = $scssCompiler->compile($inputScss);
      $autoprefixer = new \Padaliyajay\PHPAutoprefixer\Autoprefixer($css);

      if (!file_exists(dirname($outputFile))) {
        mkdir(dirname($outputFile), 0777, TRUE);
      }

      $outputCss = $autoprefixer->compile();
      \CCL::dumpFile($outputFile, $outputCss);

      $outputMinCssFile = preg_replace(';\.css$;', '.min.css', $outputFile);
      $outputMinCss = $minifier->run($outputCss);
      \CCL::dumpFile($outputMinCssFile, $outputMinCss);
    }
  }

}
