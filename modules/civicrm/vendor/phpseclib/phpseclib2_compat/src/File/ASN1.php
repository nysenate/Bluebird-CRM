<?php

/**
 * Pure-PHP ASN.1 Parser
 *
 * PHP version 5
 *
 * ASN.1 provides the semantics for data encoded using various schemes.  The most commonly
 * utilized scheme is DER or the "Distinguished Encoding Rules".  PEM's are base64 encoded
 * DER blobs.
 *
 * \phpseclib\File\ASN1 decodes and encodes DER formatted messages and places them in a semantic context.
 *
 * Uses the 1988 ASN.1 syntax.
 *
 * @category  File
 * @package   ASN1
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2012 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\File;

/**
 * Pure-PHP ASN.1 Parser
 *
 * @package ASN1
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class ASN1 extends \phpseclib3\File\ASN1
{
    /**
     * Parse BER-encoding
     *
     * Serves a similar purpose to openssl's asn1parse
     *
     * @param string $encoded
     * @return array
     * @access public
     */
    public static function decodeBER($encoded)
    {
        $decoded = parent::decodeBER($encoded);
        if ($decoded === null) {
            return [false];
        }
        return $decoded;
    }

    /**
     * BER-decode the OID
     *
     * Called by _decode_ber()
     *
     * @access private
     * @param string $content
     * @return string
     */
    public function _decodeOID($content)
    {
        return $this->decodeOID($content);
    }

    /**
     * DER-encode the length
     *
     * DER supports lengths up to (2**8)**127, however, we'll only support lengths up to (2**8)**4.  See
     * {@link http://itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf#p=13 X.690 paragraph 8.1.3} for more information.
     *
     * @access private
     * @param int $length
     * @return string
     */
    public function _encodeLength($length)
    {
        return $this->encodeLength($length);
    }

    /**
     * DER-encode the OID
     *
     * Called by _encode_der()
     *
     * @access private
     * @param string $content
     * @return string
     */
    public function _encodeOID($source)
    {
        return $this->encodeOID($source);
    }
}