<?php

/**
 * Pure-PHP implementation of Blowfish.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * Useful resources are as follows:
 *
 *  - {@link http://en.wikipedia.org/wiki/Blowfish_(cipher) Wikipedia description of Blowfish}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $blowfish = new \phpseclib\Crypt\Blowfish();
 *
 *    $blowfish->setKey('12345678901234567890123456789012');
 *
 *    $plaintext = str_repeat('a', 1024);
 *
 *    echo $blowfish->decrypt($blowfish->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @category  Crypt
 * @package   Blowfish
 * @author    Jim Wigginton <terrafrost@php.net>
 * @author    Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Crypt;

/**
 * Pure-PHP implementation of Blowfish.
 *
 * @package Blowfish
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class Blowfish extends Base
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
            case $length < 32:
                return 32;
            case $length > 448:
                return 448;
        }
        return $length;
    }
}