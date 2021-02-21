<?php
namespace CCL\Tasks;

use CCL\ErrorBuffer;
use Symfony\Component\Filesystem\Filesystem;

class Template {

  /**
   * Create PHP code using JSON data and a PHP template.
   *
   * @param array $task
   *   With keys:
   *   - tpl-file: string, the name of the template file
   *   - tpl-items: array, list of files to generate
   *     array(string $fileName => mixed $templateData)
   *
   * @link https://github.com/civicrm/composer-compile-plugin/blob/master/doc/tasks.md
   */
  public static function compile(array $task) {
    self::assertFileField($task, 'tpl-file');

    foreach ($task['tpl-items'] as $outputFile => $inputData) {
      $errorBuffer = ErrorBuffer::create()->start();

      ob_start();
      try {
        static::runFile($task['tpl-file'], ['tplData' => $inputData]);
      } finally {
        $outputData = ob_get_contents();
        ob_end_clean();
        $errorBuffer->stop();
        foreach ($errorBuffer->getLines() as $error) {
          fwrite(STDERR, "$error\n");
        }
      }

      if ($errorBuffer->isFatal()) {
        throw new \RuntimeException("Fatal template error");
      }

      // Note: 'Template' is used internally to build CCL.php, so don't call CCL::dumpFile().
      (new Filesystem())->dumpFile($outputFile, $outputData);

      unset($outputData);
      unset($errorBuffer);
    }
  }

  protected static function runFile($_tplFile, $_tplVars) {
    extract($_tplVars);
    require $_tplFile;
  }

  /**
   * @param array $task
   * @param $field
   * @return array
   */
  protected static function assertFileField(array $task, $field) {
    if (empty($task[$field]) || !file_exists($task[$field])) {
      throw new \InvalidArgumentException(sprintf(
        "Invalid file reference (%s=%s)",
        $field,
        $task[$field] ?? 'NULL'
      ));
    }
    return $task;
  }

}
