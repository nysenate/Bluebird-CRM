<?php

ini_set('memory_limit', '2G');

// Some of the following code is Bluebird specific. For the unique way that Bluebird needs to bootstrap.

// See cv() -- civix boilerplate originally initialized it to getenv('PWD')
// we need to manipualte it for Bluebird
$cv_run_dir = getenv('PWD');

define('EXT_ROOT', dirname(__DIR__ .'/../../..'));
define('BB_SCRIPT_UTILS',EXT_ROOT . '/../../../scripts/script_utils.php' );
define('DRUPAL_DIR', EXT_ROOT . '/../../../../drupal');
define('CIVICRM_SETTINGS', EXT_ROOT . '/../../../../drupal/sites/default/civicrm.settings.php');

if (file_exists(BB_SCRIPT_UTILS)) {
    define('BLUEBIRD',true);
} else {
    define('BLUEBIRD',false);
}

if (BLUEBIRD) {
    // Require that a Bluebird instance be specified on the command line
    $instance_env = 'INSTANCE';
    $instance = getenv($instance_env) ?? '';
    if (! $instance) {
        fwrite(STDERR, "ERROR: Required environment variable {$instance_env} is not set.\n");
        exit(1);
    }
    putenv('CIVICRM_SETTINGS=' . CIVICRM_SETTINGS);
    $cv_run_dir = DRUPAL_DIR;
}

// phpcs:disable
eval(cv('php:boot --level=full', $cv_run_dir, 'phpcode'));
// phpcs:enable

if (BLUEBIRD) {
    chdir(EXT_ROOT);
}

// Allow autoloading of PHPUnit helper classes in this extension.
$loader = new \Composer\Autoload\ClassLoader();
$loader->add('CRM_', [__DIR__ . '/../..', __DIR__]);
$loader->addPsr4('Civi\\', [__DIR__ . '/../../Civi', __DIR__ . '/Civi']);
$loader->add('api_', [__DIR__ . '/../..', __DIR__]);
$loader->addPsr4('api\\', [__DIR__ . '/../../api', __DIR__ . '/api']);

$loader->register();

/**
 * Call the "cv" command.
 *
 * @param string $cmd
 *   The rest of the command to send.
 * @param string $decode
 *   Ex: 'json' or 'phpcode'.
 * @return mixed
 *   Response output (if the command executed normally).
 *   For 'raw' or 'phpcode', this will be a string. For 'json', it could be any JSON value.
 * @throws \RuntimeException
 *   If the command terminates abnormally.
 */
function cv(string $cmd, string $run_dir, string $decode = 'json') {

  putenv('CV_OUTPUT=json');
  $cmd = 'cv ' . $cmd;
  $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => STDERR];
  $oldOutput = getenv('CV_OUTPUT');
  putenv('CV_OUTPUT=json');

  // Execute `cv` in the original folder. This is a work-around for
  // phpunit/codeception, which seem to manipulate PWD.
  $cmd = sprintf('cd %s; %s', escapeshellarg($run_dir), $cmd);

  $process = proc_open($cmd, $descriptorSpec, $pipes, __DIR__);
  putenv("CV_OUTPUT=$oldOutput");
  fclose($pipes[0]);
  $result = stream_get_contents($pipes[1]);
  fclose($pipes[1]);
  if (proc_close($process) !== 0) {
    throw new RuntimeException("Command failed ($cmd):\n$result");
  }
  switch ($decode) {
    case 'raw':
      return $result;

    case 'phpcode':
      // If the last output is /*PHPCODE*/, then we managed to complete execution.
      if (substr(trim($result), 0, 12) !== '/*BEGINPHP*/' || substr(trim($result), -10) !== '/*ENDPHP*/') {
        throw new \RuntimeException("Command failed ($cmd):\n$result");
      }
      return $result;

    case 'json':
      return json_decode($result, 1);

    default:
      throw new RuntimeException("Bad decoder format ($decode)");
  }
}
