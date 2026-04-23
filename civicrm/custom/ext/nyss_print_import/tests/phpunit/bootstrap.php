<?php

ini_set('memory_limit', '2G');

// Bluebird-specific bootstrap. Adapted from gov.nysenate.webintegration.

$cv_run_dir = getenv('PWD');

// Resolve the extension root (2 levels up from tests/phpunit/).
define('EXT_ROOT', realpath(__DIR__ . '/../..'));

// Walk up from EXT_ROOT to find the repo root, identified by a .git directory.
(function () {
  $dir = EXT_ROOT;
  while ($dir !== dirname($dir)) {
    if (is_dir($dir . '/.git')) {
      define('REPO_ROOT', $dir);
      return;
    }
    $dir = dirname($dir);
  }
  fwrite(STDERR, "ERROR: Could not locate repo root (no .git directory found).\n");
  exit(1);
})();

define('BB_SCRIPT_UTILS', REPO_ROOT . '/civicrm/scripts/script_utils.php');
define('DRUPAL_DIR', REPO_ROOT . '/drupal');
define('CIVICRM_SETTINGS', REPO_ROOT . '/drupal/sites/default/civicrm.settings.php');

if (file_exists(BB_SCRIPT_UTILS)) {
  define('BLUEBIRD', true);
}
else {
  define('BLUEBIRD', false);
}

if (BLUEBIRD) {
  $instance_env = 'INSTANCE';
  $instance = getenv($instance_env) ?? '';
  if (!$instance) {
    fwrite(STDERR, "ERROR: Required environment variable {$instance_env} is not set.\n");
    exit(1);
  }
  putenv('CIVICRM_SETTINGS=' . CIVICRM_SETTINGS);
  putenv('HTTP_HOST=' . $instance);
  $cv_run_dir = DRUPAL_DIR;
}

// phpcs:disable
eval(cv('php:boot --level=full', $cv_run_dir, 'phpcode'));
// phpcs:enable

if (BLUEBIRD) {
  chdir(EXT_ROOT);
}

// Allow autoloading of classes in this extension.
$loader = new \Composer\Autoload\ClassLoader();
$loader->add('CRM_', [__DIR__ . '/../..', __DIR__]);
$loader->addPsr4('Civi\\', [__DIR__ . '/../../Civi', __DIR__ . '/Civi']);
$loader->register();

/**
 * Call the "cv" command.
 *
 * @param string $cmd
 * @param string $run_dir
 * @param string $decode  'json', 'raw', or 'phpcode'
 * @return mixed
 * @throws \RuntimeException
 */
function cv(string $cmd, string $run_dir, string $decode = 'json') {
  $cmd = 'cv ' . $cmd;
  $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => STDERR];
  $oldOutput = getenv('CV_OUTPUT');
  putenv('CV_OUTPUT=json');

  $cmd = sprintf('cd %s; %s', escapeshellarg($run_dir), $cmd);

  // getenv() without args only returns the original process environment and
  // misses values set via putenv(). Merge those in explicitly using getenv('KEY').
  $env = getenv();
  foreach (['HTTP_HOST', 'CIVICRM_SETTINGS', 'INSTANCE'] as $key) {
    $val = getenv($key);
    if ($val !== FALSE) {
      $env[$key] = $val;
    }
  }
  $env['CV_OUTPUT'] = 'json';
  $process = proc_open($cmd, $descriptorSpec, $pipes, __DIR__, $env);
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
