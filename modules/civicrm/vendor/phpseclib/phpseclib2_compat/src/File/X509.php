<?php

/**
 * Pure-PHP X.509 Parser
 *
 * PHP version 5
 *
 * Encode and decode X.509 certificates.
 *
 * The extensions are from {@link http://tools.ietf.org/html/rfc5280 RFC5280} and
 * {@link http://web.archive.org/web/19961027104704/http://www3.netscape.com/eng/security/cert-exts.html Netscape Certificate Extensions}.
 *
 * Note that loading an X.509 certificate and resaving it may invalidate the signature.  The reason being that the signature is based on a
 * portion of the certificate that contains optional parameters with default values.  ie. if the parameter isn't there the default value is
 * used.  Problem is, if the parameter is there and it just so happens to have the default value there are two ways that that parameter can
 * be encoded.  It can be encoded explicitly or left out all together.  This would effect the signature value and thus may invalidate the
 * the certificate all together unless the certificate is re-signed.
 *
 * @category  File
 * @package   X509
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2012 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\File;

use phpseclib\Crypt\RSA;
use phpseclib3\File\ASN1\Element as NewElement;
use phpseclib\File\ASN1\Element as OldElement;

/**
 * Pure-PHP X.509 Parser
 *
 * @package X509
 * @method bool|mixed[] loadX509(string $cert, int $mode = X509::FORMAT_AUTO_DETECT)
 * @method string|false saveX509(mixed[] $cert, int $format = X509::FORMAT_PEM)
 * @method bool loadCA(string $cert)
 * @method bool validateURL(string $url)
 * @method bool validateDate(\DateTimeInterface|string $date = null)
 * @method ?bool validateSignature(bool $caonly = true)
 * @method static void setRecurLimit(int $count)
 * @method static void disableURLFetch()
 * @method static void enableURLFetch()
 * @method static string decodeIP(string $ip)
 * @method static array{bool|string, bool|string} decodeNameConstraintIP(string $ip)
 * @method static string encodeIP(string|array{string, string} $ip)
 * @method bool setDNProp(string $propName, mixed $propValue, string $type = 'utf8String')
 * @method void removeDNProp(string $propName)
 * @method mixed[] getDNProp(string $propName, mixed[] $dn, bool $withType = false)
 * @method bool setDN(mixed $dn, bool $merge = false, string $type = 'utf8String')
 * @method mixed getDN(int $format = X509::DN_ARRAY, mixed[] $dn = null)
 * @method mixed getIssuerDN(int $format = X509::DN_ARRAY)
 * @method mixed getSubjectDN(int $format = X509::DN_ARRAY)
 * @method mixed getIssuerDNProp(string $propName, bool $withType = false)
 * @method mixed getSubjectDNProp(string $propName, bool $withType = false)
 * @method mixed[] getChain()
 * @method bool|mixed[] getCurrentCert()
 * @method void setChallenge(string $challenge)
 * @method PublicKey|false getPublicKey()
 * @method bool|mixed[] loadCSR(string $csr, int $mode = X509::FORMAT_AUTO_DETECT)
 * @method string|false saveCSR(array $csr, int $format = X509::FORMAT_PEM)
 * @method bool|mixed[] loadSPKAC(string $spkac)
 * @method string|false saveSPKAC(array $spkac, int $format = X509::FORMAT_PEM)
 * @method bool|mixed[] loadCRL(string $crl, int $mode = X509::FORMAT_AUTO_DETECT)
 * @method string|false saveCRL(array $crl, int $format = X509::FORMAT_PEM)
 * @method bool|mixed[] sign(X509 $issuer, X509 $subject)
 * @method bool|mixed[] signCSR()
 * @method bool|mixed[] signSPKAC()
 * @method bool|mixed[] signCRL(X509 $issuer, X509 $crl)
 * @method void setStartDate(\DateTimeInterface|string $date)
 * @method void setEndDate(\DateTimeInterface|string $date)
 * @method void setSerialNumber(string $serial, int $base = -256)
 * @method void makeCA()
 * @method bool removeExtension(string $id)
 * @method mixed getExtension(string $id, mixed[] $cert = null, string $path = null)
 * @method mixed[] getExtension(mixed[] $cert = null, string $path = null)
 * @method bool setExtension(mixed[] $cert = null, mixed $value, string $path = null)
 * @method bool removeAttribute(string $id, int $disposition = X509::ATTR_ALL)
 * @method mixed getAttribute(string $id, int $disposition = X509::ATTR_ALL, array $csr = null)
 * @method mixed[] getAttributes(mixed[] $csr = null)
 * @method void setKeyIdentifier(string $value)
 * @method mixed computeKeyIdentifier(mixed $key = null, int $method = 1)
 * @method void setDomain(string ...$domains)
 * @method void setIPAddress(mixed ...$ipAddresses)
 * @method bool revoke(string $serial, string $date = null)
 * @method bool unrevoke(string $serial)
 * @method mixed getRevoked(string $serial)
 * @method mixed[] listRevoked(mixed[] $crl = null)
 * @method bool removeRevokedCertificateExtension(string $serial, string $id)
 * @method mixed getRevokedCertificateExtension(string $serial, string $id, mixed[] $crl = null)
 * @method bool|mixed[] getRevokedCertificateExtensions(string $serial, mixed[] $crl = null)
 * @method bool setRevokedCertificateExtension(string $serial, string $id, $value, bool $critical = false, bool $replace = true)
 * @method static void registerExtension(string $id, mixed[] $mapping)
 * @method static ?mixed[] getRegisteredExtension(string $id)
 * @method static void setExtensionValue(string $id, $value, bool $critical = false, bool $replace = false)
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class X509
{
    /**
     * Flag to only accept signatures signed by certificate authorities
     *
     * Not really used anymore but retained all the same to suppress E_NOTICEs from old installs
     *
     * @access public
     */
    const VALIDATE_SIGNATURE_BY_CA = 1;

    /**#@+
     * @access public
     * @see \phpseclib3\File\X509::getDN()
    */
    /**
     * Return internal array representation
     */
    const DN_ARRAY = 0;
    /**
     * Return string
     */
    const DN_STRING = 1;
    /**
     * Return ASN.1 name string
     */
    const DN_ASN1 = 2;
    /**
     * Return OpenSSL compatible array
     */
    const DN_OPENSSL = 3;
    /**
     * Return canonical ASN.1 RDNs string
     */
    const DN_CANON = 4;
    /**
     * Return name hash for file indexing
     */
    const DN_HASH = 5;
    /**#@-*/

    /**#@+
     * @access public
     * @see \phpseclib3\File\X509::saveX509()
     * @see \phpseclib3\File\X509::saveCSR()
     * @see \phpseclib3\File\X509::saveCRL()
    */
    /**
     * Save as PEM
     *
     * ie. a base64-encoded PEM with a header and a footer
     */
    const FORMAT_PEM = 0;
    /**
     * Save as DER
     */
    const FORMAT_DER = 1;
    /**
     * Save as a SPKAC
     *
     * Only works on CSRs. Not currently supported.
     */
    const FORMAT_SPKAC = 2;
    /**
     * Auto-detect the format
     *
     * Used only by the load*() functions
     */
    const FORMAT_AUTO_DETECT = 3;
    /**#@-*/

    /**
     * Attribute value disposition.
     * If disposition is >= 0, this is the index of the target value.
     */
    const ATTR_ALL = -1; // All attribute values (array).
    const ATTR_APPEND = -2; // Add a value.
    const ATTR_REPLACE = -3; // Clear first, then add a value.

    /**
     * The X509 object
     *
     * @var \phpseclib3\File\X509
     * @access private
     */
    private $x509;

    /**
     * Default Constructor.
     *
     * @return \phpseclib\File\X509
     * @access public
     */
    public function __construct()
    {
        // we don't extend phpseclib3\File\X509 because the setPublicKey() and setPrivateKey() methods
        // have different method signatures
        $this->x509 = new \phpseclib3\File\X509();
    }

    /**
     *  __call() magic method
     *
     * @access public
     */
    public function __call($name, $args)
    {
        foreach ($args as &$arg) {
            if ($arg instanceof \phpseclib\File\X509) {
                $arg = $arg->x509;
            }
        }

        switch ($name) {
            case 'loadX509':
            case 'saveX509':
            case 'sign':
                $part1 = 'tbsCertificate';
                $part2 = 'extensions';
                break;
            case 'loadCRL':
            case 'saveCRL':
            case 'signCRL':
                $part1 = 'tbsCertList';
                $part2 = 'crlExtensions';
                break;
            case 'loadCSR':
            case 'saveCSR':
            case 'signCSR':
                $part1 = 'certificationRequestInfo';
                $part2 = 'attributes';
        }

        if (isset($args[0])) {
            switch ($name) {
                case 'saveX509':
                case 'saveCRL':
                case 'saveCSR':
                    if (isset($args[0][$part1][$part2])) {
                        $arr = &$args[0][$part1][$part2];
                        if ($part2 == 'attributes') {
                            foreach ($arr as &$attr) {
                                if (isset($attr['type']) && $attr['type'] == 'pkcs-9-at-extensionRequest') {
                                    $arr = $attr['value'][0];
                                    break;
                                }
                            }
                        }
                        foreach ($arr as &$extension) {
                            if ($extension instanceof NewElement || !is_array($extension)) {
                                continue;
                            }
                            if (is_string($extension['extnValue'])) {
                                $extension['extnValue'] = base64_decode($extension['extnValue']);
                            }
                        }
                    }

                    if (isset($args[0]['signature'])) {
                        $args[0]['signature'] = base64_decode($args[0]['signature']);
                    }
            }
        }

        $result = $this->x509->$name(...$args);
        if ($result instanceof \phpseclib3\File\X509) {
            $temp = new static;
            $temp->x509 = $result;
            return $temp;
        }

        if (!is_array($result)) {
            return $result;
        }

        $result = self::replaceNewElements($result);

        if (!isset($part1)) {
            return $result;
        }

        if (isset($result[$part1][$part2])) {
            $arr = &$result[$part1][$part2];
            if ($part2 == 'attributes') {
                foreach ($arr as &$attr) {
                    if (isset($attr['type']) && $attr['type'] == 'pkcs-9-at-extensionRequest') {
                        $arr = $attr['value'][0];
                        break;
                    }
                }
            }
            foreach ($arr as &$extension) {
                if ($extension instanceof NewElement || !is_array($extension)) {
                    continue;
                }
                if (is_string($extension['extnValue'])) {
                    $extension['extnValue'] = base64_encode($extension['extnValue']);
                }
            }
        }

        if (isset($result['signature'])) {
            $result['signature'] = base64_encode($result['signature']);
        }

        return $result;
    }

    /**
     *  __callStatic() magic method
     *
     * @access public
     */
    public static function __callStatic($name, $args)
    {
        return \phpseclib3\File\X509::$name(...$args);
    }

    /**
     * Set public key
     *
     * Key needs to be a \phpseclib\Crypt\RSA object
     *
     * @param object $key
     * @access public
     * @return bool
     */
    public function setPublicKey($key)
    {
        if (!$key instanceof RSA) {
            return;
        }
        $key = $key->getKeyObject();
        if ($key instanceof \phpseclib3\Crypt\Common\PublicKey) {
            if ($key instanceof \phpseclib3\Crypt\RSA) {
                $key = $key->withPadding(\phpseclib3\Crypt\RSA::SIGNATURE_PKCS1);
            }
            $this->x509->setPublicKey($key);
        }
    }

    /**
     * Set private key
     *
     * Key needs to be a \phpseclib\Crypt\RSA object
     *
     * @param object $key
     * @access public
     */
    public function setPrivateKey($key)
    {
        if (!$key instanceof RSA) {
            return;
        }
        $key = $key->getKeyObject();
        if ($key instanceof \phpseclib3\Crypt\Common\PrivateKey) {
            if ($key instanceof \phpseclib3\Crypt\RSA) {
                $key = $key->withPadding(\phpseclib3\Crypt\RSA::SIGNATURE_PKCS1);
            }
            $this->x509->setPrivateKey($key);
        }
    }

    /**
     * Returns the OID corresponding to a name
     *
     * What's returned in the associative array returned by loadX509() (or load*()) is either a name or an OID if
     * no OID to name mapping is available. The problem with this is that what may be an unmapped OID in one version
     * of phpseclib may not be unmapped in the next version, so apps that are looking at this OID may not be able
     * to work from version to version.
     *
     * This method will return the OID if a name is passed to it and if no mapping is avialable it'll assume that
     * what's being passed to it already is an OID and return that instead. A few examples.
     *
     * getOID('2.16.840.1.101.3.4.2.1') == '2.16.840.1.101.3.4.2.1'
     * getOID('id-sha256') == '2.16.840.1.101.3.4.2.1'
     * getOID('zzz') == 'zzz'
     *
     * @access public
     * @return string
     */
    public function getOID($name)
    {
        return \phpseclib3\File\ASN1::getOID($name);
    }

    /**
     * Replaces \phpseclib3\File\ASN1\Element with \phpseclib\File\ASN1\Element
     *
     * @return array
     */
    private static function replaceNewElements($el)
    {
        switch (true) {
            case $el instanceof NewElement:
                return new OldElement($el->element);
            case !is_array($el):
                return $el;
        }

        foreach ($el as &$val) {
            $val = self::replaceNewElements($val);
        }

        return $el;
    }
}