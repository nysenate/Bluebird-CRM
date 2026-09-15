<?php

/**
 * Pure-PHP implementation of RC2.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * Useful resources are as follows:
 *
 *  - {@link http://tools.ietf.org/html/rfc2268}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $rc2 = new \phpseclib\Crypt\RC2();
 *
 *    $rc2->setKey('abcdefgh');
 *
 *    $plaintext = str_repeat('a', 1024);
 *
 *    echo $rc2->decrypt($rc2->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @category Crypt
 * @package  RC2
 * @author   Patrick Monnerat <pm@datasphere.ch>
 * @license  http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link     http://phpseclib.sourceforge.net
 */

namespace phpseclib\Crypt;

/**
 * Pure-PHP implementation of RC2.
 *
 * @package RC2
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class RC2 extends Base
{
    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.
     *
     * $mode could be:
     *
     * - self::MODE_ECB
     *
     * - self::MODE_CBC
     *
     * - self::MODE_CTR
     *
     * - self::MODE_CFB
     *
     * - self::MODE_OFB
     *
     * If not explicitly set, self::MODE_CBC will be used.
     *
     * @param int $mode
     * @access public
     */
    public function __construct($mode = self::MODE_CBC)
    {
        parent::__construct($mode);
        $this->key_length = 8;
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
            case $length < 8:
                return 8;
            case $length > 1024:
                return 1024;
        }

        return $length;
    }

    /**
     * Setup IV and key
     */
    protected function setup()
    {
        if ($this->explicit_key_length) {
            $this->cipher->setKeyLength($this->key_length);
        }
        $this->cipher->setKey($this->key);
        if (!$this->ivSet) {
            $this->setIV('');
        }
        $this->changed = false;
    }

    /**
     * Sets the key.
     *
     * Keys can be of any length. RC2, itself, uses 8 to 1024 bit keys (eg.
     * strlen($key) <= 128), however, we only use the first 128 bytes if $key
     * has more then 128 bytes in it, and set $key to a single null byte if
     * it is empty.
     *
     * If the key is not explicitly set, it'll be assumed to be a single
     * null byte.
     *
     * @see \phpseclib\Crypt\Base::setKey()
     * @access public
     * @param string $key
     * @param int $t1 optional Effective key length in bits.
     */
    function setKey($key, $t1 = 0)
    {
        parent::setKey($key);
        if ($t1) {
            $this->setKeyLength($t1);
        }
    }
}