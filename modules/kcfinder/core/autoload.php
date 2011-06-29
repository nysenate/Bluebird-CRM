<?php

/** This file is part of KCFinder project
  *
  *      @desc Autoload classes magic function
  *   @package KCFinder
  *   @version 2.3
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// PHP VERSION CHECK
if (substr(PHP_VERSION, 0, strpos(PHP_VERSION, '.')) < 5)
    die("You are using PHP " . PHP_VERSION . " when KCFinder require at least version 5! Some systems has an option to change the active PHP version. Please refer to your hosting provider or upgrade your PHP distribution.");

// MAGIC AUTOLOAD CLASSES FUNCTION
function __autoload($class) {
    if ($class == "uploader")
        require "core/uploader.php";
    elseif ($class == "browser")
        require "core/browser.php";
    elseif (file_exists("core/types/$class.php"))
        require "core/types/$class.php";
    elseif (file_exists("lib/class_$class.php"))
        require "lib/class_$class.php";
    elseif (file_exists("lib/helper_$class.php"))
        require "lib/helper_$class.php";
}

// json_encode() IMPLEMENTATION IF JSON EXTENSION IS MISSING
if (!function_exists("json_encode")) {

    function kcfinder_json_string_encode($string) {
        return '"' .
            str_replace('/', "\\/",
            str_replace("\t", "\\t",
            str_replace("\r", "\\r",
            str_replace("\n", "\\n",
            str_replace('"', "\\\"",
            str_replace("\\", "\\\\",
        $string)))))) . '"';
    }

    function json_encode($data) {

        if (is_array($data)) {
            $ret = array();

            // OBJECT
            if (array_keys($data) !== range(0, count($data) - 1)) {
                foreach ($data as $key => $val)
                    $ret[] = kcfinder_json_string_encode($key) . ':' . json_encode($val);
                return "{" . implode(",", $ret) . "}";

            // ARRAY
            } else {
                foreach ($data as $val)
                    $ret[] = json_encode($val);
                return "[" . implode(",", $ret) . "]";
            }

        // BOOLEAN OR NULL
        } elseif (is_bool($data) || ($data === null))
            return ($data === null)
                ? "null"
                : ($data ? "true" : "false");

        // NUMBER
        elseif (is_int($data) || is_float($data))
            return $data;

        // STRING
        return kcfinder_json_string_encode($data);
    }
}

?>