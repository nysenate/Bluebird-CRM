<?php

/**
 * Configuration class
 *
 * Reads and processes command line options and configuration file (INI)
 * settings.  Available CLI options are defined in a static structure.  Any
 * arguments without a matching definition entry are ignored.  Options may
 * also be stored in a configuration file (defaults to config.ini), or
 * passed in during runtime.
 *
 * This class should be extended for the application using it.  The child
 * class must provide its own definition for $_available_command_options.
 * Note that options already populated in self::$_standard_command_options
 * are automatically added to the parser, but may be overridden.
 *
 * The order of precedence for found options, from least to greatest:
 *   1. default values
 *   2. values passed to the constructor
 *   3. values read from the configuration file
 *   4. values read from the command line
 */
class ConfigBase {
  const CONFIG_VALUE_NONE = 0;
  const CONFIG_VALUE_REQUIRED = 1;
  const CONFIG_VALUE_OPTIONAL = 2;

  /**
   * Standard options.  The available options array uses the same structure:
   *   array( <long_option_name> = array(
   *            'index' => <string>,
   *            'val'   => ( 0 | 1 | 2 ),
   *            'label' => <string>,
   *            'short' => <character>,
   *            'description' => <string>
   *            )
   *   )
   * 'index'       = the index name to use in Config->options.  If not provided,
   *                 defaults to <long_option_name>.  In all cases, the value
   *                 is scrubbed to replace all non-alpha characters with '_',
   *                 and formatted as all lower-case.
   * 'val'         = if a value is required (0 = No, 1 = Yes, 2 = Optional).  If
   *                 not provided, it is set to zero.
   * 'label'       = a user-friendly name for this option.  If not provided, it
   *                 is set to index.
   * 'short'       = an optional, single-character shortcut (e.g., -h vs --help).
   *                 If not provided, it is set to empty string.
   * 'description' = a user-friendly description for this option.  If not provided
   *                 it is set to empty string.
   * 'required'    = indicates if this option must be specified (by CLI or file).
   *                 If not provided, it is set to FALSE.
   * 'default'     = default value assigned if no other value is provided.  If
   *                 not provided, it is set to NULL.
   **/
  protected static $_standard_command_options = array(
    'config-file' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Config file',
      'description' => "File holding all runtime configurations",
      'required' => FALSE,
      'default' => 'config.ini'
    ),
    'test-config' => array(
      'val' => self::CONFIG_VALUE_NONE,
      'label' => 'Test configuration settings',
      'description' => "Loads and parses configuration, then reports findings and exits",
      'required' => FALSE,
    ),
    'help' => array(
      'val' => self::CONFIG_VALUE_NONE,
      'label' => 'Help',
      'description' => 'This help text',
      'required' => FALSE,
    ),
    'user' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'User',
      'description' => "The Bluebird user through whom this script should authenticate",
      'short' => 'U',
    ),
    'pass' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Pass',
      'description' => "The password for the Bluebird user used to authenticate this script",
      'short' => 'P',
    ),
    'site' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Site',
      'description' => 'The Bluebird site designation to run against',
      'short' => 'S',
      'required' => TRUE,
    ),
    'key' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Key',
      'description' => 'Optional string to assign to REQUEST[\'key\'] variable',
      'short' => 'K',
    ),
  );

  // Instance property for singleton pattern
  protected static $_instance = array();

  // command line options assigned by child classes.
  protected $_available_command_options = array();

  // Collection of options used at runtime.
  protected $_runtime_command_options = array();

  // Resolved configuration array
  protected $options = array();

  // Raw command line options
  protected $cli_options = array();

  // Ensures config file is only read once.
  private $_config_loaded = FALSE;

  // Switch for long or short usage text
  public $short_usage = TRUE;

  /**
   * Config constructor.
   * @param array $options
   */
  protected function __construct(Array $options = array()) {
    // consolidate available options into the runtime selection
    $this->_runtime_command_options = array_merge(
      static::$_standard_command_options,
      $this->_available_command_options
    );
    array_walk($this->_runtime_command_options, function (&$v, $k) {
      $v['passed'] = FALSE;
    });

    // Make sure the runtime options are in a usable state, and retrieve defaults
    $defaults = array_map(
      function ($v) {
        return $v['default'];
      },
      $this->polishOptions()
    );

    // initialize CLI options
    $this->cli_options = ((php_sapi_name() == 'cli') ? $this->parseCliOptions() : array());
    if (array_ifelse('help', $this->cli_options, FALSE)) {
      $this->short_usage = FALSE;
      die($this->getUsage());
    }

    // Merge the options so far to get the proper value of config_file
    $this->options = array_merge($defaults, $options);
    $this->options['config_file'] = array_ifelse('config_file', $this->cli_options, $this->options['config_file']);

    // read the config file
    $conf_options = $this->readConfig();

    // Re-merge to get the complete set of options
    $this->options = array_merge($this->options, $conf_options, $this->cli_options);

    // validate required config
    $this->validateConfig();
  }

  public function __get($name) {
    $ret = array_key_exists($name, $this->options) ? $this->options[$name] : NULL;
    return $ret;
  }

  /**
   * @param array $options
   * @return ConfigBase
   */
  public static function getInstance(Array $options = array()) {
    $class_name = get_called_class();
    if (array_key_exists($class_name, self::$_instance)) {
      $ret = self::$_instance[$class_name];
    }
    else {
      $ret = new $class_name($options);
      self::$_instance[$class_name] = $ret;
    }
    if ($options) {
      $ret->options = array_merge($ret->options, $options);
    }
    return $ret;
  }

  public function getOptions() {
    return $this->options;
  }

  public function getUsage($style = NULL) {
    if (is_null($style)) {
      $style = $this->short_usage;
    }

    if ($style) {
      return $this->getShortUsage();
    } else {
      return $this->getLongUsage();
    }
  }

  public function getLongUsage() {
    $msg = array();
    $req = array();
    foreach ($this->_runtime_command_options as $key => $val) {
      $onemsg = '';
      if (array_ifelse('short', $val)) {
        $onemsg = "-{$val['short']} | ";
      }
      $onemsg .= "--{$key}";
      if (!array_ifelse('required', $val)) {
        $onemsg = "[$onemsg]";
      }
      else {
        $onemsg .= "  (required)";
      }
      $onemsg .= " {$val['label']}\n{$val['description']}";
      if (array_ifelse('default', $val)) {
        $onemsg .= " Default: {$val['default']}";
      }
      if (!array_ifelse('required', $val)) {
        $msg[] = $onemsg;
      }
      else {
        $req[] = $onemsg;
      }
    }
    $final_msg = "\nUsage: \n\n" . implode("\n\n", $req);
    if (count($req) || count($msg)) {
      $final_msg .= "\n\n";
    }
    $final_msg .= implode("\n\n", $msg) . "\n";
    return $final_msg;
  }

  public function getShortUsage() {
    $msg = array();
    $req = array();
    foreach ($this->_runtime_command_options as $key => $val) {
      $onemsg = '';
      if (array_ifelse('short', $val)) {
        $onemsg = "-{$val['short']}|";
      }
      $onemsg .= "--{$key}";
      if (array_ifelse('val', $val) > self::CONFIG_VALUE_NONE) {
        $submsg = "<{$val['label']}>";
        if ($val['val'] == self::CONFIG_VALUE_OPTIONAL) {
          $submsg = "[{$submsg}]";
        }
        $onemsg .= " {$submsg}";
      }
      if (!array_ifelse('required', $val)) {
        $msg[] = "[$onemsg]";
      }
      else {
        $req[] = $onemsg;
      }
    }
    return "\nUsage: \n\n" . implode(" ", $req) . " " . implode(" ", $msg) . "\n";
  }

  protected function parseCliOptions() {
    // string of short option characters, e.g., -c -h -a
    $shortopts = '';
    // array of long option names, e.g., --help --config
    $longopts = array();
    // a convenience array to reverse map short options to long options
    $opt_map = array();

    // Iterate through the collection of options to build the short/long lists.
    foreach ($this->_runtime_command_options as $key => $val) {
      // Force-type the 'val' property to integer.
      // 0 = Not required, 1 = Required, 2 = Optional
      $v = (int) $val['val'];

      // Create the long option entry.
      $longopts[] = $key .
        ($v > self::CONFIG_VALUE_NONE ? ':' : '') .
        ($v > self::CONFIG_VALUE_REQUIRED ? ':' : '');

      // If the long option defines a shortcut, create the short option entry.
      // Also, add it to the reverse map for future reference.
      if ($val['short']) {
        $shortopts .= $val['short'] .
          ($v > self::CONFIG_VALUE_NONE ? ':' : '') .
          ($v > self::CONFIG_VALUE_REQUIRED ? ':' : '');
        $opt_map[$val['short']] = $key;
      }
    }

    // Parse the command line parameters
    $cmd_options = getopt($shortopts, $longopts);

    // Initialize the return variable
    $ret = array();

    // For each entry in $cmd_options, translate the option into its
    // config equivalent.
    foreach ($cmd_options as $key => $val) {
      // The long key is either in the reverse map array, or it is the actual key.
      $long_key = array_key_exists($key, $opt_map) ? $opt_map[$key] : $key;

      // If the long key exists, add it to the return
      if (array_key_exists($long_key, $this->_runtime_command_options)) {
        $current_option = $this->_runtime_command_options[$long_key];
        $found_index = $current_option['index'];

        // The return key is the 'index' property of this option's definition.
        $ret[$found_index] = $val;

        // Options which don't require values (and options with unpassed
        // optional values) are set to boolean false by getopt().  Correct
        // this to show the option is selected.
        if ($current_option['val'] == self::CONFIG_VALUE_NONE ||
          ($current_option['val'] == self::CONFIG_VALUE_OPTIONAL && !$ret[$found_index])
        ) {
          $ret[$found_index] = TRUE;
        }

        // Record that this option was passed in
        $this->_runtime_command_options[$long_key]['passed'] = TRUE;
      }
    }

    return $ret;
  }

  /**
   * Washes the runtime command options array to make sure its entries play nice.
   */
  protected function polishOptions() {
    $ret = array();
    foreach ($this->_runtime_command_options as $key => $val) {
      // Make sure the index exists, and that it has a friendly name.
      $val['index'] = strtolower(
        preg_replace(
          '/[^a-z]/i',
          '_',
          array_ifelse('index', $val, $key)
        )
      );

      // Scrub the remaining elements
      $val['val'] = (int) array_ifelse('val', $val);
      $val['label'] = array_ifelse('label', $val, $val['index']);
      $val['short'] = (string) array_ifelse('short', $val);
      $val['description'] = (string) array_ifelse('description', $val);
      $val['required'] = (boolean) array_ifelse('required', $val);
      $val['default'] = array_ifelse('default', $val);

      // Add the polished version to the return
      $this->_runtime_command_options[$key] = $val;
      $ret[$val['index']] = $val;
    }
    return $ret;
  }

  protected function readConfig($refresh = FALSE) {
    // Prep the return
    $ret = array();

    // Set the config filename to be used
    $ini_file = array_ifelse('config_file', $this->options, 'config.ini');

    // Read the configuration file, if necessary
    if (!$this->_config_loaded || $refresh) {
      $ini = @parse_ini_file($ini_file);
      $this->_config_loaded = TRUE;
      if ($ini) {
        foreach ($ini as $key => $val) {
          // Don't allow the config file to reset the config_file option.
          if ($key !== 'config_file') {
            $ret[$key] = $val;
          }
        }
      }
      /* else {
        echo "WARNING: Config file '{$ini_file}' is missing or blank!\n";
      } */
    }
    return $ret;
  }

  protected function validateConfig($error_on_invalid = TRUE) {
    // Don't throw an exception if testing the config.
    if ($this->options['test_config']) {
      $error_on_invalid = FALSE;
    }

    // Test for required values.
    $has_error = array();
    foreach ($this->_runtime_command_options as $key => $one_option) {
      $current_option = array_ifelse($one_option['index'], $this->options, NULL);
      if (is_null($current_option)) {
        if ($one_option['required']) {
          echo "ERROR: Missing config parameter $key!\n";
          $has_error[] = $key;
        }
        elseif ($one_option['val'] == self::CONFIG_VALUE_REQUIRED && $one_option['passed']) {
          echo "ERROR: Config parameter $key missing required value!\n";
          $has_error[] = $key;
        }
      }
    }

    // If there's an error, and we care, throw an exception
    if (count($has_error) && $error_on_invalid) {
      echo $this->getUsage() . "\n\n";
      $msg = implode(',', $has_error);
      throw new RuntimeException("Invalid/Missing configuration items: $msg");
    }

    // If the config is being tested, report and die()
    if ($this->options['test_config']) {
      if (!$has_error) {
        echo "Configuration tested OK\n" . var_export($this->options, 1) . "\n\n";
      }
      else {
        echo "Configuration test FAILED!\n";
      }
      die();
    }
  }

}