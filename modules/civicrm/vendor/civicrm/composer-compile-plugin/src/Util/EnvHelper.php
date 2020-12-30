<?php
namespace Civi\CompilePlugin\Util;

class EnvHelper
{

    /**
     * @return string[]
     *   Key-value pairs.
     */
    public static function getAll()
    {
        // Huzza, PHP 7.1
        return getenv();
    }

    /**
     * Set the full environment, precisely.
     *
     * @param array $newEnv
     *   The new environment. Key-value pairs.
     *   All other environment variables will be removed.
     */
    public static function setAll($newEnv)
    {
        $currentEnv = self::getAll();
        $allKeys = array_unique(array_merge(array_keys($currentEnv), array_keys($newEnv)));
        foreach ($allKeys as $key) {
            if (!isset($currentEnv[$key])) {
                putenv("$key=" . $newEnv[$key]);
            } elseif (!isset($newEnv[$key])) {
                putenv("$key");
            } elseif ($currentEnv[$key] !== $newEnv[$key]) {
                putenv("$key=" . $newEnv[$key]);
            }
        }
    }

    /**
     * Add variables to the environment.
     *
     * @param array $vars
     *   The new environment. Key-value pairs.
     */
    public static function add($vars)
    {
        foreach ($vars as $key => $value) {
            putenv("$key=$value");
        }
    }
}
