<?php

/**
 * Pure-PHP implementations of keyed-hash message authentication codes (HMACs) and various cryptographic hashing functions.
 *
 * Uses hash() or mhash() if available and an internal implementation, otherwise.  Currently supports the following:
 *
 * md2, md5, md5-96, sha1, sha1-96, sha256, sha256-96, sha384, and sha512, sha512-96
 *
 * If {@link self::setKey() setKey()} is called, {@link self::hash() hash()} will return the HMAC as opposed to
 * the hash.  If no valid algorithm is provided, sha1 will be used.
 *
 * PHP version 5
 *
 * {@internal The variable names are the same as those in
 * {@link http://tools.ietf.org/html/rfc2104#section-2 RFC2104}.}}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $hash = new \phpseclib\Crypt\Hash('sha1');
 *
 *    $hash->setKey('abcdefg');
 *
 *    echo base64_encode($hash->hash('abcdefg'));
 * ?>
 * </code>
 *
 * @category  Crypt
 * @package   Hash
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Crypt;

/**
 * Pure-PHP implementations of keyed-hash message authentication codes (HMACs) and various cryptographic hashing functions.
 *
 * @package Hash
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class Hash
{
    /**#@+
     * @access private
     * @see \phpseclib\Crypt\Hash::__construct()
     */
    /**
     * Toggles the internal implementation
     */
    const MODE_INTERNAL = 1;
    /**#@-*/

    /**
     * Hash Object
     *
     * @see self::setHash()
     * @var null|\phpseclib3\Crypt\Hash
     * @access private
     */
    private $hash;

    /**
     * Default Constructor.
     *
     * @param string $hash
     * @return \phpseclib\Crypt\Hash
     * @access public
     */
    public function __construct($hash = 'sha1')
    {
        $this->setHash($hash);
    }

    /**
     * Sets the key for HMACs
     *
     * Keys can be of any length.
     *
     * @access public
     * @param string $key
     */
    public function setKey($key = false)
    {
        $this->hash->setKey($key);
    }

    /**
     * Gets the hash function.
     *
     * As set by the constructor or by the setHash() method.
     *
     * @access public
     * @return string
     */
    public function getHash()
    {
        return $this->hash->getHash();
    }

    /**
     * Sets the hash function.
     *
     * @access public
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = new \phpseclib3\Crypt\Hash;
        try {
            $this->hash->setHash($hash);
        } catch (\phpseclib3\Exception\UnsupportedAlgorithmException $e) {
            $this->hash->setHash('sha1');
        }
    }

    /**
     * Compute the HMAC.
     *
     * @access public
     * @param string $text
     * @return string
     */
    public function hash($text)
    {
        return $this->hash->hash($text);
    }

    /**
     * Returns the hash length (in bytes)
     *
     * @access public
     * @return int
     */
    public function getLength()
    {
        return $this->hash->getLengthInBytes();
    }
}