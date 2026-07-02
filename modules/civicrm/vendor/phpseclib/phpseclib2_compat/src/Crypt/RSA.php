<?php

/**
 * Pure-PHP PKCS#1 (v2.1) compliant implementation of RSA.
 *
 * PHP version 5
 *
 * Here's an example of how to encrypt and decrypt text with this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $rsa = new \phpseclib\Crypt\RSA();
 *    extract($rsa->createKey());
 *
 *    $plaintext = 'terrafrost';
 *
 *    $rsa->loadKey($privatekey);
 *    $ciphertext = $rsa->encrypt($plaintext);
 *
 *    $rsa->loadKey($publickey);
 *    echo $rsa->decrypt($ciphertext);
 * ?>
 * </code>
 *
 * Here's an example of how to create signatures and verify signatures with this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $rsa = new \phpseclib\Crypt\RSA();
 *    extract($rsa->createKey());
 *
 *    $plaintext = 'terrafrost';
 *
 *    $rsa->loadKey($privatekey);
 *    $signature = $rsa->sign($plaintext);
 *
 *    $rsa->loadKey($publickey);
 *    echo $rsa->verify($plaintext, $signature) ? 'verified' : 'unverified';
 * ?>
 * </code>
 *
 * @category  Crypt
 * @package   RSA
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2009 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Crypt;

use phpseclib3\Crypt\RSA as RSA2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Exception\UnsupportedAlgorithmException;
use phpseclib3\Exception\UnsupportedFormatException;
use phpseclib3\Exception\NoKeyLoadedException;
use phpseclib3\Crypt\Common\Formats\Keys\PuTTY;
use phpseclib3\Crypt\Common\Formats\Keys\OpenSSH;
use phpseclib3\Math\BigInteger;
use phpseclib\Math\BigInteger as BigInteger2;

/**
 * Pure-PHP PKCS#1 compliant implementation of RSA.
 *
 * @package RSA
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class RSA
{
    /**#@+
     * @access public
     * @see self::encrypt()
     * @see self::decrypt()
     */
    /**
     * Use {@link http://en.wikipedia.org/wiki/Optimal_Asymmetric_Encryption_Padding Optimal Asymmetric Encryption Padding}
     * (OAEP) for encryption / decryption.
     *
     * Uses sha1 by default.
     *
     * @see self::setHash()
     * @see self::setMGFHash()
     */
    const ENCRYPTION_OAEP = 1;
    /**
     * Use PKCS#1 padding.
     *
     * Although self::ENCRYPTION_OAEP offers more security, including PKCS#1 padding is necessary for purposes of backwards
     * compatibility with protocols (like SSH-1) written before OAEP's introduction.
     */
    const ENCRYPTION_PKCS1 = 2;
    /**
     * Do not use any padding
     *
     * Although this method is not recommended it can none-the-less sometimes be useful if you're trying to decrypt some legacy
     * stuff, if you're trying to diagnose why an encrypted message isn't decrypting, etc.
     */
    const ENCRYPTION_NONE = 3;
    /**#@-*/

    /**#@+
     * @access public
     * @see self::sign()
     * @see self::verify()
     * @see self::setHash()
    */
    /**
     * Use the Probabilistic Signature Scheme for signing
     *
     * Uses sha1 by default.
     *
     * @see self::setSaltLength()
     * @see self::setMGFHash()
     */
    const SIGNATURE_PSS = 1;
    /**
     * Use the PKCS#1 scheme by default.
     *
     * Although self::SIGNATURE_PSS offers more security, including PKCS#1 signing is necessary for purposes of backwards
     * compatibility with protocols (like SSH-2) written before PSS's introduction.
     */
    const SIGNATURE_PKCS1 = 2;
    /**#@-*/

    /**#@+
     * @access public
     * @see \phpseclib\Crypt\RSA::createKey()
     * @see \phpseclib\Crypt\RSA::setPrivateKeyFormat()
    */
    /**
     * PKCS#1 formatted private key
     *
     * Used by OpenSSH
     */
    const PRIVATE_FORMAT_PKCS1 = 0;
    /**
     * PuTTY formatted private key
     */
    const PRIVATE_FORMAT_PUTTY = 1;
    /**
     * XML formatted private key
     */
    const PRIVATE_FORMAT_XML = 2;
    /**
     * PKCS#8 formatted private key
     */
    const PRIVATE_FORMAT_PKCS8 = 8;
    /**
     * OpenSSH formatted private key
     */
    const PRIVATE_FORMAT_OPENSSH = 9;
    /**#@-*/

    /**#@+
     * @access public
     * @see \phpseclib\Crypt\RSA::createKey()
     * @see \phpseclib\Crypt\RSA::setPublicKeyFormat()
    */
    /**
     * Raw public key
     *
     * An array containing two \phpseclib\Math\BigInteger objects.
     *
     * The exponent can be indexed with any of the following:
     *
     * 0, e, exponent, publicExponent
     *
     * The modulus can be indexed with any of the following:
     *
     * 1, n, modulo, modulus
     */
    const PUBLIC_FORMAT_RAW = 3;
    /**
     * PKCS#1 formatted public key (raw)
     *
     * Used by File/X509.php
     *
     * Has the following header:
     *
     * -----BEGIN RSA PUBLIC KEY-----
     *
     * Analogous to ssh-keygen's pem format (as specified by -m)
     */
    const PUBLIC_FORMAT_PKCS1 = 4;
    const PUBLIC_FORMAT_PKCS1_RAW = 4;
    /**
     * XML formatted public key
     */
    const PUBLIC_FORMAT_XML = 5;
    /**
     * OpenSSH formatted public key
     *
     * Place in $HOME/.ssh/authorized_keys
     */
    const PUBLIC_FORMAT_OPENSSH = 6;
    /**
     * PKCS#1 formatted public key (encapsulated)
     *
     * Used by PHP's openssl_public_encrypt() and openssl's rsautl (when -pubin is set)
     *
     * Has the following header:
     *
     * -----BEGIN PUBLIC KEY-----
     *
     * Analogous to ssh-keygen's pkcs8 format (as specified by -m). Although PKCS8
     * is specific to private keys it's basically creating a DER-encoded wrapper
     * for keys. This just extends that same concept to public keys (much like ssh-keygen)
     */
    const PUBLIC_FORMAT_PKCS8 = 7;
    /**#@-*/

    /**
     * The Original Key
     *
     * @see self::getComment()
     * @var string
     * @access private
     */
    private $origKey = null;

    /**
     * The Key
     *
     * @var \phpseclib3\Crypt\Common\AsymmetricKey
     * @access private
     */
    private $key = null;

    /**
     * Password
     *
     * @var string
     * @access private
     */
    private $password = false;

    /**
     * Private Key Format
     *
     * @var int
     * @access private
     */
    private $privateKeyFormat = self::PRIVATE_FORMAT_PKCS1;

    /**
     * Public Key Format
     *
     * @var int
     * @access public
     */
    private $publicKeyFormat = self::PUBLIC_FORMAT_PKCS1;

    /**
     * Public key comment field.
     *
     * @var string
     * @access private
     */
    private $comment = 'phpseclib-generated-key';

    /**
     * Encryption mode
     *
     * @var int
     * @access private
     */
    private $encryptionMode = self::ENCRYPTION_OAEP;

    /**
     * Signature mode
     *
     * @var int
     * @access private
     */
    private $signatureMode = self::SIGNATURE_PSS;

    /**
     * Hash name
     *
     * @var string
     * @access private
     */
    private $hash = 'sha1';

    /**
     * Hash function for the Mask Generation Function
     *
     * @var string
     * @access private
     */
    private $mgfHash = 'sha1';

    /**
     * Length of salt
     *
     * @var int
     * @access private
     */
    private $sLen;

    /**
     * The constructor
     *
     * @return \phpseclib\Crypt\RSA
     * @access public
     */
    public function __construct()
    {
        // don't do anything
    }

    /**
     * Create public / private key pair
     *
     * Returns an array with the following three elements:
     *  - 'privatekey': The private key.
     *  - 'publickey':  The public key.
     *  - 'partialkey': A partially computed key (if the execution time exceeded $timeout).
     *                  Will need to be passed back to \phpseclib\Crypt\RSA::createKey() as the third parameter for further processing.
     *
     * @access public
     * @param int $bits
     */
    public function createKey($bits = 1024)
    {
        $privatekey = RSA2::createKey($bits);

        return [
            'privatekey' => $privatekey,
            'publickey' => $privatekey->getPublicKey(),
            'partialkey' => false
        ];
    }

    /**
     * Returns the key size
     *
     * More specifically, this returns the size of the modulo in bits.
     *
     * @access public
     * @return int
     */
    public function getSize()
    {
        // for EC and RSA keys this'll return an integer
        // for DSA keys this'll return an array (L + N)
        return isset($this->key) ? $this->key->getLength() : 0;
    }

    /**
     * Sets the password
     *
     * Private keys can be encrypted with a password.  To unset the password, pass in the empty string or false.
     * Or rather, pass in $password such that empty($password) && !is_string($password) is true.
     *
     * @see self::createKey()
     * @see self::loadKey()
     * @access public
     * @param string $password
     */
    public function setPassword($password = false)
    {
        $this->password = $password;
    }

    /**
     * Loads a public or private key
     *
     * Returns true on success and false on failure (ie. an incorrect password was provided or the key was malformed)
     *
     * @access public
     * @param string|RSA|array $key
     * @param bool|int $type optional
     * @return bool
     */
    public function loadKey($key)
    {
        if ($key instanceof AsymmetricKey) {
            $this->key = $key;
        } else if ($key instanceof RSA) {
            $this->key = $key->key;
        } else {
            try {
                if (is_array($key)) {
                    foreach ($key as &$value) {
                        if ($value instanceof BigInteger2) {
                            $value = new BigInteger($value->toBytes(true), -256);
                        }
                    }
                }
                $this->key = PublicKeyLoader::load($key, $this->password);
            } catch (NoKeyLoadedException $e) {
                $this->key = $this->origKey = null;
                return false;
            }
            $this->origKey = $key;
        }

        // with phpseclib 2.0 loading a key does not reset any of the following
        // so we'll need to preserve the old settings whenever a new key is loaded
        // with this shim
        $this->setEncryptionMode($this->encryptionMode);
        //$this->setSignatureMode($this->signatureMode);
        $this->setHash($this->hash);
        $this->setMGFHash($this->mgfHash);
        $this->setSaltLength($this->sLen);

        return true;
    }

    /**
     *  __toString() magic method
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        PuTTY::setComment($this->comment);
        OpenSSH::setComment($this->comment);

        if ($this->key instanceof PublicKey) {
            return $this->key->toString(self::const2str($this->publicKeyFormat));
        }

        if ($this->key instanceof PrivateKey) {
            try {
                return $this->key->toString(self::const2str($this->privateKeyFormat));
            } catch (UnsupportedFormatException $e) {
                if ($this->password) {
                    return $this->key->withPassword()->toString(self::const2str($this->privateKeyFormat));
                }
            }
        }

        return '';
    }

    /**
     * Defines the public key
     *
     * Some private key formats define the public exponent and some don't.  Those that don't define it are problematic when
     * used in certain contexts.  For example, in SSH-2, RSA authentication works by sending the public key along with a
     * message signed by the private key to the server.  The SSH-2 server looks the public key up in an index of public keys
     * and if it's present then proceeds to verify the signature.  Problem is, if your private key doesn't include the public
     * exponent this won't work unless you manually add the public exponent. phpseclib tries to guess if the key being used
     * is the public key but in the event that it guesses incorrectly you might still want to explicitly set the key as being
     * public.
     *
     * Do note that when a new key is loaded the index will be cleared.
     *
     * Returns true on success, false on failure
     *
     * @see self::getPublicKey()
     * @access public
     * @param string $key optional
     * @param int $type optional
     * @return bool
     */
    public function setPublicKey()
    {
        return false;
    }

    /**
     * Defines the private key
     *
     * If phpseclib guessed a private key was a public key and loaded it as such it might be desirable to force
     * phpseclib to treat the key as a private key. This function will do that.
     *
     * Do note that when a new key is loaded the index will be cleared.
     *
     * Returns true on success, false on failure
     *
     * @see self::getPublicKey()
     * @access public
     * @param string $key optional
     * @param int $type optional
     * @return bool
     */
    public function setPrivateKey($key = false)
    {
        if ($key === false && $this->key instanceof RSA2) {
            $this->key = $this->key->asPrivateKey();
        }

        try {
            $key = PublicKeyLoader::load($key);
        } catch (NoKeyLoadedException $e) {
            return false;
        }
        if ($key instanceof RSA2) { 
            $this->key = $key instanceof PublicKey ? $key->asPrivateKey() : $key;
            return true;
        }

        return false;
    }

    /**
     * Returns the public key
     *
     * The public key is only returned under two circumstances - if the private key had the public key embedded within it
     * or if the public key was set via setPublicKey().  If the currently loaded key is supposed to be the public key this
     * function won't return it since this library, for the most part, doesn't distinguish between public and private keys.
     *
     * @see self::getPublicKey()
     * @access public
     * @param string $key
     * @param int $type optional
     */
    public function getPublicKey($type = self::PUBLIC_FORMAT_PKCS8)
    {
        PuTTY::setComment($this->comment);
        OpenSSH::setComment($this->comment);

        if ($this->key instanceof PrivateKey) {
            return $this->key->getPublicKey()->toString(self::const2str($type));
        }

        if ($this->key instanceof PublicKey) {
            return $this->key->toString(self::const2str($type));
        }

        return false;
    }

    /**
     * Returns the public key's fingerprint
     *
     * The public key's fingerprint is returned, which is equivalent to running `ssh-keygen -lf rsa.pub`. If there is
     * no public key currently loaded, false is returned.
     * Example output (md5): "c1:b1:30:29:d7:b8:de:6c:97:77:10:d7:46:41:63:87" (as specified by RFC 4716)
     *
     * @access public
     * @param string $algorithm The hashing algorithm to be used. Valid options are 'md5' and 'sha256'. False is returned
     * for invalid values.
     * @return mixed
     */
    public function getPublicKeyFingerprint($algorithm = 'md5')
    {
        if ($this->key instanceof PublicKey) {
            return $this->key->getFingerprint($algorithm);
        }

        return false;
    }

    /**
     * Returns the private key
     *
     * The private key is only returned if the currently loaded key contains the constituent prime numbers.
     *
     * @see self::getPublicKey()
     * @access public
     * @param string $key
     * @param int $type optional
     * @return mixed
     */
    public function getPrivateKey($type = self::PUBLIC_FORMAT_PKCS1)
    {
        PuTTY::setComment($this->comment);
        OpenSSH::setComment($this->comment);

        if ($this->key instanceof PrivateKey) {
            try {
                return $this->key->toString(self::const2str($this->privateKeyFormat));
            } catch (UnsupportedFormatException $e) {
                if ($this->password) {
                    return $this->key->withPassword()->toString(self::const2str($this->privateKeyFormat));
                }
            }

        }

        return false;
    }

    /**
     *  __clone() magic method
     *
     * @access public
     * @return Crypt_RSA
     */
    public function __clone()
    {
        $key = new RSA();
        $key->loadKey($this);
        return $key;
    }

    /**
     * Convert phpseclib 2.0 style constants to phpseclib 3.0 style strings
     *
     * @param int $const
     * @access private
     * @return string
     */
    private static function const2str($const)
    {
        switch ($const) {
            case self::PRIVATE_FORMAT_PKCS1:
            case self::PUBLIC_FORMAT_PKCS1:
                return 'PKCS1';
            case self::PRIVATE_FORMAT_PUTTY:
                return 'PuTTY';
            case self::PRIVATE_FORMAT_XML:
            case self::PUBLIC_FORMAT_XML:
                return 'XML';
            case self::PRIVATE_FORMAT_PKCS8:
            case self::PUBLIC_FORMAT_PKCS8:
                return 'PKCS8';
            case self::PRIVATE_FORMAT_OPENSSH:
            case self::PUBLIC_FORMAT_OPENSSH:
                return 'OpenSSH';
        }
    }

    /**
     * Determines the private key format
     *
     * @see self::createKey()
     * @access public
     * @param int $format
     */
    public function setPrivateKeyFormat($format)
    {
        $this->privateKeyFormat = $format;
    }

    /**
     * Determines the public key format
     *
     * @see self::createKey()
     * @access public
     * @param int $format
     */
    public function setPublicKeyFormat($format)
    {
        $this->publicKeyFormat = $format;
    }

    /**
     * Determines which hashing function should be used
     *
     * Used with signature production / verification and (if the encryption mode is self::ENCRYPTION_OAEP) encryption and
     * decryption.  If $hash isn't supported, sha1 is used.
     *
     * @access public
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        if ($this->key instanceof AsymmetricKey) {
            try {
                $this->key = $this->key->withHash($hash);
            } catch (UnsupportedAlgorithmException $e) {
                $this->key = $this->key->withHash('sha1');
            }
        }
    }

    /**
     * Determines which hashing function should be used for the mask generation function
     *
     * The mask generation function is used by self::ENCRYPTION_OAEP and self::SIGNATURE_PSS and although it's
     * best if Hash and MGFHash are set to the same thing this is not a requirement.
     *
     * @access public
     * @param string $hash
     */
    public function setMGFHash($hash)
    {
        $this->mgfHash = $hash;
        if ($this->key instanceof RSA2) {
            try {
                $this->key = $this->key->withMGFHash($hash);
            } catch (UnsupportedAlgorithmException $e) {
                $this->key = $this->key->withMGFHash('sha1');
            }
        }
    }

    /**
     * Determines the salt length
     *
     * To quote from {@link http://tools.ietf.org/html/rfc3447#page-38 RFC3447#page-38}:
     *
     *    Typical salt lengths in octets are hLen (the length of the output
     *    of the hash function Hash) and 0.
     *
     * @access public
     * @param int $format
     */
    public function setSaltLength($sLen)
    {
        $this->sLen = $sLen;
        if ($this->key instanceof RSA2) {
            $this->key = $this->key->withSaltLength($sLen);
        }
    }

    /**
     * Set Encryption Mode
     *
     * Valid values include self::ENCRYPTION_OAEP and self::ENCRYPTION_PKCS1.
     *
     * @access public
     * @param int $mode
     */
    public function setEncryptionMode($mode)
    {
        $this->encryptionMode = $mode;
        if ($this->key instanceof RSA2) {
            $this->key = $this->key->withPadding(
                self::enc2pad($this->encryptionMode) |
                self::sig2pad($this->signatureMode)
            );
        }
    }

    /**
     * Set Signature Mode
     *
     * Valid values include self::SIGNATURE_PSS and self::SIGNATURE_PKCS1
     *
     * @access public
     * @param int $mode
     */
    public function setSignatureMode($mode)
    {
        $this->signatureMode = $mode;
        if ($this->key instanceof RSA2) {
            $this->key = $this->key->withPadding(
                self::enc2pad($this->encryptionMode) |
                self::sig2pad($this->signatureMode)
            );
        }
    }

    /**
     * Convert phpseclib 2.0 style constants to phpseclib 3.0 style constants
     *
     * @param int $mode
     * @access private
     * @return int
     */
    private function enc2pad($mode)
    {
        switch ($mode) {
           case self::ENCRYPTION_PKCS1:
               return RSA2::ENCRYPTION_PKCS1;
           case self::ENCRYPTION_NONE:
               return RSA2::ENCRYPTION_NONE;
           //case self::ENCRYPTION_OAEP:
           default:
               return RSA2::ENCRYPTION_OAEP;
        }
    }

    /**
     * Convert phpseclib 2.0 style constants to phpseclib 3.0 style constants
     *
     * @param int $mode
     * @access private
     * @return int
     */
    private function sig2pad($mode)
    {
        switch ($mode) {
           case self::SIGNATURE_PKCS1:
               return RSA2::SIGNATURE_PKCS1;
           //case self::SIGNATURE_PSS:
           default:
               return RSA2::SIGNATURE_PSS;
        }
    }

    /**
     * Set public key comment.
     *
     * @access public
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get public key comment.
     *
     * @access public
     * @return string
     */
    public function getComment()
    {
        // we'd need to make the load method in the parent PuTTY and OpenSSH classes public instead of protected
        // for this to work
        try {
            $key = PuTTY::load($this->origKey);
            return $key['comment'];
        } catch (\Exception $e) {}

        try {
            $key = OpenSSH::load($this->origKey);
            return $key['comment'];
        } catch (\Exception $e) {}

        return '';
    }

    /**
     * Encryption
     *
     * Both self::ENCRYPTION_OAEP and self::ENCRYPTION_PKCS1 both place limits on how long $plaintext can be.
     * If $plaintext exceeds those limits it will be broken up so that it does and the resultant ciphertext's will
     * be concatenated together.
     *
     * @see self::decrypt()
     * @access public
     * @param string $plaintext
     * @return string
     */
    public function encrypt($plaintext)
    {
        if (!$this->key instanceof RSA2) {
            return false;
        }
        $key = $this->key;
        if ($key instanceof PrivateKey) {
            $key = $key->toString('Raw');
            $temp = new static();
            $temp->loadKey(['e' => $key['d'], 'n' => $key['n']]);
            $key = $temp->key;
        }
        if ($key instanceof PublicKey) {
            switch ($this->encryptionMode) {
                case self::ENCRYPTION_PKCS1:
                    $len = ($key->getLength() - 88) >> 3;
                    break;
                case self::ENCRYPTION_NONE:
                    $len = $key->getLength() >> 3;
                    break;
                //case self::ENCRYPTION_OAEP:
                default:
                    $len = ($key->getLength() - 2 * $key->getHash()->getLength() - 16) >> 3;
            }
            $plaintext = str_split($plaintext, $len);
            $ciphertext = '';
            foreach ($plaintext as $m) {
                $ciphertext.= $key->encrypt($m);
            }
            return $ciphertext;
        }

        return false;
    }

    /**
     * Decryption
     *
     * @see self::encrypt()
     * @access public
     * @param string $plaintext
     * @return string
     */
    public function decrypt($ciphertext)
    {
        if (!$this->key instanceof RSA2) {
            return false;
        }
        $key = $this->key;
        if ($key instanceof PublicKey) {
            $key = $key->asPrivateKey();
        }
        if ($key instanceof PrivateKey) {
            $len = $key->getLength() >> 3;
            $ciphertext = str_split($ciphertext, $len);
            $ciphertext[count($ciphertext) - 1] = str_pad($ciphertext[count($ciphertext) - 1], $len, chr(0), STR_PAD_LEFT);

            $plaintext = '';
            foreach ($ciphertext as $c) {
                try {
                    $plaintext.= $key->decrypt($c);
                } catch (\Exception $e) {
                    return false;
                }
            }
            return $plaintext;
        }

        return false;
    }

    /**
     * Create a signature
     *
     * @see self::verify()
     * @access public
     * @param string $message
     * @return string
     */
    public function sign($message)
    {
        if ($this->key instanceof PrivateKey) {
            return $this->key->sign($message);
        }

        return false;
    }

    /**
     * Verifies a signature
     *
     * @see self::sign()
     * @access public
     * @param string $message
     * @param string $signature
     * @return bool
     */
    public function verify($message, $signature)
    {
        if ($this->key instanceof PublicKey) {
            return $this->key->verify($message, $signature);
        }

        return false;
    }

    /**
     * Returns a public key object
     *
     * @access public
     * @return AsymmetricKey|false
     */
    public function getKeyObject()
    {
        return $this->key;
    }
}