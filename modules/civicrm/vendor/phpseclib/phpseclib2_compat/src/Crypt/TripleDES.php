<?php

/**
 * Pure-PHP implementation of Triple DES.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.  Operates in the EDE3 mode (encrypt-decrypt-encrypt).
 *
 * PHP version 5
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $des = new \phpseclib\Crypt\TripleDES();
 *
 *    $des->setKey('abcdefghijklmnopqrstuvwx');
 *
 *    $size = 10 * 1024;
 *    $plaintext = '';
 *    for ($i = 0; $i < $size; $i++) {
 *        $plaintext.= 'a';
 *    }
 *
 *    echo $des->decrypt($des->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @category  Crypt
 * @package   TripleDES
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Crypt;

/**
 * Pure-PHP implementation of Triple DES.
 *
 * @package TripleDES
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class TripleDES extends Base
{
    /**
     * Encrypt / decrypt using inner chaining
     *
     * Inner chaining is used by SSH-1 and is generally considered to be less secure then outer chaining (self::MODE_CBC3).
     */
    const MODE_3CBC = -2;

    /**
     * Encrypt / decrypt using outer chaining
     *
     * Outer chaining is used by SSH-2 and when the mode is set to \phpseclib\Crypt\Base::MODE_CBC.
     */
    const MODE_CBC3 = Base::MODE_CBC;

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.
     *
     * $mode could be:
     *
     * - \phpseclib\Crypt\Base::MODE_ECB
     *
     * - \phpseclib\Crypt\Base::MODE_CBC
     *
     * - \phpseclib\Crypt\Base::MODE_CTR
     *
     * - \phpseclib\Crypt\Base::MODE_CFB
     *
     * - \phpseclib\Crypt\Base::MODE_OFB
     *
     * - \phpseclib\Crypt\TripleDES::MODE_3CBC
     *
     * If not explicitly set, \phpseclib\Crypt\Base::MODE_CBC will be used.
     *
     * @see \phpseclib\Crypt\DES::__construct()
     * @see \phpseclib\Crypt\Base::__construct()
     * @param int $mode
     * @access public
     */
    public function __construct($mode = self::MODE_CBC)
    {
        if ($mode == self::MODE_3CBC) {
            $this->cipher = new \phpseclib3\Crypt\TripleDES('3cbc');
            $this->key_length = $this->cipher->getKeyLength();
            return;
        }
        parent::__construct($mode);
    }

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
            case $length <= 64:
                return 64;
            case $length <= 128:
                return 128;
            default:
                return 192;
        }
    }
}