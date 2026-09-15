<?php

/**
 * Pure-PHP implementation of Twofish.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * Useful resources are as follows:
 *
 *  - {@link http://en.wikipedia.org/wiki/Twofish Wikipedia description of Twofish}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $twofish = new \phpseclib\Crypt\Twofish();
 *
 *    $twofish->setKey('12345678901234567890123456789012');
 *
 *    $plaintext = str_repeat('a', 1024);
 *
 *    echo $twofish->decrypt($twofish->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @category  Crypt
 * @package   Twofish
 * @author    Jim Wigginton <terrafrost@php.net>
 * @author    Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Crypt;

/**
 * Pure-PHP implementation of Twofish.
 *
 * @package Twofish
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class Twofish extends Base
{
    /**
     * Turns key lengths, be they valid or invalid, to valid key lengths
     *
     * @param int $length
     * @access private
     * @return int
     */
    protected function calculateNewKeyLength($length)
    {
        switch (true) {
            case $length <= 128:
                return 128;
            case $length <= 192:
                return 192;
            default:
                return 256;
        }
    }
}