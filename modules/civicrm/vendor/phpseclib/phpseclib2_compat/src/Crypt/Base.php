<?php

/**
 * Base Class for all \phpseclib\Crypt\* cipher classes
 *
 * PHP version 5
 *
 * @category  Crypt
 * @package   Base
 * @author    Jim Wigginton <terrafrost@php.net>
 * @author    Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Crypt;

use phpseclib3\Exception\BadDecryptionException;
use phpseclib3\Exception\InconsistentSetupException;

/**
 * Base Class for all \phpseclib\Crypt\* cipher classes
 *
 * @package Base
 * @author  Jim Wigginton <terrafrost@php.net>
 * @author  Hans-Juergen Petrich <petrich@tronic-media.com>
 */
abstract class Base
{
    /**#@+
     * @access public
     * @see \phpseclib\Crypt\Base::encrypt()
     * @see \phpseclib\Crypt\Base::decrypt()
     */
    /**
     * Encrypt / decrypt using the Counter mode.
     *
     * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
     */
    const MODE_CTR = -1;
    /**
     * Encrypt / decrypt using the Electronic Code Book mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
     */
    const MODE_ECB = 1;
    /**
     * Encrypt / decrypt using the Code Book Chaining mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
     */
    const MODE_CBC = 2;
    /**
     * Encrypt / decrypt using the Cipher Feedback mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher_feedback_.28CFB.29
     */
    const MODE_CFB = 3;
    /**
     * Encrypt / decrypt using the Cipher Feedback mode (8bit)
     */
    const MODE_CFB8 = 38;
    /**
     * Encrypt / decrypt using the Output Feedback mode (8bit)
     */
    const MODE_OFB8 = 7;
    /**
     * Encrypt / decrypt using the Output Feedback mode.
     *
     * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Output_feedback_.28OFB.29
     */
    const MODE_OFB = 4;
    /**
     * Encrypt / decrypt using streaming mode.
     */
    const MODE_STREAM = 5;
    /**
     * Encrypt / decrypt using Galois/Counter mode.
     *
     * @link https://en.wikipedia.org/wiki/Galois/Counter_Mode
     */
    const MODE_GCM = 6;
    /**#@-*/

    /**#@+
     * @access private
     * @see \phpseclib3\Crypt\Common\SymmetricKey::__construct()
     */
    /**
     * Base value for the internal implementation $engine switch
     */
    const ENGINE_INTERNAL = 1;
    /**
     * Base value for the eval() implementation $engine switch
     */
    const ENGINE_EVAL = 4;
    /**
     * Base value for the mcrypt implementation $engine switch
     */
    const ENGINE_MCRYPT = 2;
    /**
     * Base value for the openssl implementation $engine switch
     */
    const ENGINE_OPENSSL = 3;
    /**
     * Base value for the libsodium implementation $engine switch
     */
    const ENGINE_LIBSODIUM = 5;
    /**
     * Base value for the openssl / gcm implementation $engine switch
     */
    const ENGINE_OPENSSL_GCM = 6;
    /**#@-*/

    /**
     * Engine Map
     *
     * @access private
     * @see \phpseclib3\Crypt\Common\SymmetricKey::getEngine()
     */
    const ENGINE_MAP = [
        self::ENGINE_INTERNAL    => 'PHP',
        self::ENGINE_EVAL        => 'Eval',
        self::ENGINE_MCRYPT      => 'mcrypt',
        self::ENGINE_OPENSSL     => 'OpenSSL',
        self::ENGINE_LIBSODIUM   => 'libsodium',
        self::ENGINE_OPENSSL_GCM => 'OpenSSL (GCM)'
    ];

    /**
     * The Cipher
     *
     * @var \phpseclib3\Crypt\Common\SymmetricKey
     * @access private
     */
    protected $cipher;

    /**
     * The Key
     *
     * @see self::setKey()
     * @var string
     * @access private
     */
    protected $key = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    /**
     * Password Parameters
     *
     * @see self::setPassword()
     * @var array
     * @access private
     */
    protected $password = [];

    /**
     * The Key Length (in bytes)
     *
     * @see self::setKeyLength()
     * @var int
     * @access private
     */
    protected $key_length = 128;

    /**
     * Does internal cipher state need to be (re)initialized?
     *
     * @see self::setKey()
     * @var bool
     * @access private
     */
    private $changed = true;

    /**
     * Has the IV been set?
     *
     * @var bool
     * @access private
     */
    protected $ivSet = false;

    /**
     * Has the key length been explictly set?
     *
     * @var bool
     * @access private
     */
    protected $explicit_key_length = false;

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
        $map = [
            self::MODE_CTR => 'ctr',
            self::MODE_ECB => 'ecb',
            self::MODE_CBC => 'cbc',
            self::MODE_CFB => 'cfb',
            self::MODE_CFB8 => 'cfb8',
            self::MODE_OFB => 'ofb',
            self::MODE_OFB8 => 'ofb8',
            self::MODE_GCM => 'gcm',
            self::MODE_STREAM => 'stream'
        ];
        if (!isset($map[$mode])) {
            $mode = self::MODE_CBC;
        }
        $class = new \ReflectionClass(static::class);
        $class = "phpseclib3\\Crypt\\" . $class->getShortName();
        $this->cipher = new $class($map[$mode]);
        $this->key_length = $this->cipher->getKeyLength();
    }

    /**
     * Sets the initialization vector. (optional)
     *
     * SetIV is not required when self::MODE_ECB (or ie for AES: \phpseclib\Crypt\AES::MODE_ECB) is being used.  If not explicitly set, it'll be assumed
     * to be all zero's.
     *
     * @access public
     * @param string $iv
     * @internal Can be overwritten by a sub class, but does not have to be
     */
    public function setIV($iv)
    {
        $this->ivSet = true;

        if (!$this->cipher->usesIV()) {
            return;
        }

        $length = $this->cipher->getBlockLengthInBytes();
        $iv = str_pad(substr($iv, 0, $length), $length, "\0");

        try {
            $this->cipher->setIV($iv);
        } catch (\Exception $e) {}
    }

    /**
     * Sets the key length.
     *
     * Keys with explicitly set lengths need to be treated accordingly
     *
     * @access public
     * @param int $length
     */
    public function setKeyLength($length)
    {
        // algorithms that have a fixed key length should override this with a method that does nothing
        $this->changed = true;
        $this->key_length = static::calculateNewKeyLength($length);
        $this->explicit_key_length = true;
    }

    /**
     * Returns the current key length in bits
     *
     * @access public
     * @return int
     */
    public function getKeyLength()
    {
        return $this->key_length;
    }

    /**
     * Returns the current block length in bits
     *
     * @access public
     * @return int
     */
    public function getBlockLength()
    {
        return $this->cipher->getBlockLength();
    }

    /**
     * Sets the key.
     *
     * The min/max length(s) of the key depends on the cipher which is used.
     * If the key not fits the length(s) of the cipher it will paded with null bytes
     * up to the closest valid key length.  If the key is more than max length,
     * we trim the excess bits.
     *
     * If the key is not explicitly set, it'll be assumed to be all null bytes.
     *
     * @access public
     * @param string $key
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    public function setKey($key)
    {
        $this->key = $key;
        $this->password = [];
        if (!$this->explicit_key_length) {
            $this->key_length = static::calculateNewKeyLength(strlen($key) << 3);
        }
        $this->changed = true;
    }

    /**
     * Sets the password.
     *
     * Depending on what $method is set to, setPassword()'s (optional) parameters are as follows:
     *     {@link http://en.wikipedia.org/wiki/PBKDF2 pbkdf2} or pbkdf1:
     *         $hash, $salt, $count, $dkLen
     *
     *         Where $hash (default = sha1) currently supports the following hashes: see: Crypt/Hash.php
     *
     * @see Crypt/Hash.php
     * @param string $password
     * @param string $method
     * @return bool
     * @access public
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    public function setPassword($password, $method = 'pbkdf2')
    {
        $this->password = func_get_args();
        $this->cipher->setKeyLength($this->key_length);
        $this->cipher->setPassword(...func_get_args());
    }

    /**
     * Encrypts a message.
     *
     * $plaintext will be padded with additional bytes such that it's length is a multiple of the block size. Other cipher
     * implementations may or may not pad in the same manner.  Other common approaches to padding and the reasons why it's
     * necessary are discussed in the following
     * URL:
     *
     * {@link http://www.di-mgt.com.au/cryptopad.html http://www.di-mgt.com.au/cryptopad.html}
     *
     * An alternative to padding is to, separately, send the length of the file.  This is what SSH, in fact, does.
     * strlen($plaintext) will still need to be a multiple of the block size, however, arbitrary values can be added to make it that
     * length.
     *
     * @see self::decrypt()
     * @access public
     * @param string $plaintext
     * @return string $ciphertext
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    public function encrypt($plaintext)
    {
        if ($this->changed) {
            $this->setup();
        }

        try {
            return $this->cipher->encrypt($plaintext);
        } catch (\LengthException $e) {
            user_error($e->getMessage());
            $this->cipher->enablePadding();
            return $this->cipher->encrypt($plaintext);
        }
    }

    /**
     * Decrypts a message.
     *
     * If strlen($ciphertext) is not a multiple of the block size, null bytes will be added to the end of the string until
     * it is.
     *
     * @see self::encrypt()
     * @access public
     * @param string $ciphertext
     * @return string $plaintext
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    public function decrypt($ciphertext)
    {
        if ($this->changed) {
            $this->setup();
        }

        try {
            return $this->cipher->decrypt($ciphertext);
        } catch (\LengthException $e) {
            $len = strlen($ciphertext);
            $block_size = $this->cipher->getBlockLengthInBytes();
            $ciphertext = str_pad($ciphertext, $len + ($block_size - $len % $block_size) % $block_size, chr(0));
            try {
                return $this->cipher->decrypt($ciphertext);
            } catch (BadDecryptionException $e) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Setup IV and key
     */
    protected function setup()
    {
        // we set this just in case it was already set to anything via setPassword()
        $temp = $this->explicit_key_length;
        $this->setKeyLength($this->key_length);
        $this->explicit_key_length = $temp;
        if ($this->explicit_key_length) {
            $this->cipher->setKeyLength($this->key_length);
        }
        if (empty($this->password)) {
            $key_length = $this->key_length >> 3;
            $key = str_pad(substr($this->key, 0, $key_length), $key_length, "\0");
            $this->cipher->setKey($key);
        } else {
            $this->cipher->setPassword(...$this->password);
        }
        if (!$this->ivSet) {
            $this->setIV('');
        }
        $this->changed = false;
    }

    /**
     * Pad "packets".
     *
     * Block ciphers working by encrypting between their specified [$this->]block_size at a time
     * If you ever need to encrypt or decrypt something that isn't of the proper length, it becomes necessary to
     * pad the input so that it is of the proper length.
     *
     * Padding is enabled by default.  Sometimes, however, it is undesirable to pad strings.  Such is the case in SSH,
     * where "packets" are padded with random bytes before being encrypted.  Unpad these packets and you risk stripping
     * away characters that shouldn't be stripped away. (SSH knows how many bytes are added because the length is
     * transmitted separately)
     *
     * @see self::disablePadding()
     * @access public
     */
    public function enablePadding()
    {
        $this->cipher->enablePadding();
    }

    /**
     * Do not pad packets.
     *
     * @see self::enablePadding()
     * @access public
     */
    public function disablePadding()
    {
        $this->cipher->disablePadding();
    }

    /**
     * Treat consecutive "packets" as if they are a continuous buffer.
     *
     * Say you have a 32-byte plaintext $plaintext.  Using the default behavior, the two following code snippets
     * will yield different outputs:
     *
     * <code>
     *    echo $rijndael->encrypt(substr($plaintext,  0, 16));
     *    echo $rijndael->encrypt(substr($plaintext, 16, 16));
     * </code>
     * <code>
     *    echo $rijndael->encrypt($plaintext);
     * </code>
     *
     * The solution is to enable the continuous buffer.  Although this will resolve the above discrepancy, it creates
     * another, as demonstrated with the following:
     *
     * <code>
     *    $rijndael->encrypt(substr($plaintext, 0, 16));
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     * <code>
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     *
     * With the continuous buffer disabled, these would yield the same output.  With it enabled, they yield different
     * outputs.  The reason is due to the fact that the initialization vector's change after every encryption /
     * decryption round when the continuous buffer is enabled.  When it's disabled, they remain constant.
     *
     * Put another way, when the continuous buffer is enabled, the state of the \phpseclib\Crypt\*() object changes after each
     * encryption / decryption round, whereas otherwise, it'd remain constant.  For this reason, it's recommended that
     * continuous buffers not be used.  They do offer better security and are, in fact, sometimes required (SSH uses them),
     * however, they are also less intuitive and more likely to cause you problems.
     *
     * @see self::disableContinuousBuffer()
     * @access public
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    public function enableContinuousBuffer()
    {
        try {
            $this->cipher->enableContinuousBuffer();
        } catch (\BadMethodCallException $e) {
            user_error($e->getMessage());
        }
    }

    /**
     * Treat consecutive packets as if they are a discontinuous buffer.
     *
     * The default behavior.
     *
     * @see self::enableContinuousBuffer()
     * @access public
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    public function disableContinuousBuffer()
    {
        $this->cipher->disableContinuousBuffer();
    }

    /**
     * Test for engine validity
     *
     * @see self::__construct()
     * @param int $engine
     * @access public
     * @return bool
     */
    public function isValidEngine($engine)
    {
        $map = self::ENGINE_MAP;
        return $this->cipher->isValidEngine($map[$engine]);
    }

    /**
     * Sets the preferred crypt engine
     *
     * Currently, $engine could be:
     *
     * - \phpseclib\Crypt\Base::ENGINE_OPENSSL  [very fast]
     *
     * - \phpseclib\Crypt\Base::ENGINE_MCRYPT   [fast]
     *
     * - \phpseclib\Crypt\Base::ENGINE_INTERNAL [slow]
     *
     * If the preferred crypt engine is not available the fastest available one will be used
     *
     * @see self::__construct()
     * @param int $engine
     * @access public
     */
    public function setPreferredEngine($engine)
    {
        $map = self::ENGINE_MAP;
        $this->cipher->setPreferredEngine($map[$engine]);
    }

    /**
     * Returns the engine currently being utilized
     *
     * @see self::_setEngine()
     * @access public
     */
    public function getEngine()
    {
        static $reverseMap;
        if (!isset($reverseMap)) {
            $reverseMap = array_flip(self::ENGINE_MAP);
        }
        return $reverseMap[$this->cipher->getEngine()];
    }
}