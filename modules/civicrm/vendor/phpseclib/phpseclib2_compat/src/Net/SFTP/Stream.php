<?php

/**
 * SFTP Stream Wrapper
 *
 * Creates an sftp:// protocol handler that can be used with, for example, fopen(), dir(), etc.
 *
 * PHP version 5
 *
 * @category  Net
 * @package   SFTP
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2013 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Net\SFTP;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;

/**
 * SFTP Stream Wrapper
 *
 * @package SFTP
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class Stream extends \phpseclib3\Net\SFTP\Stream
{
    /**
     * Path Parser
     *
     * Extract a path from a URI and actually connect to an SSH server if appropriate
     *
     * If "notification" is set as a context parameter the message code for successful login is
     * NET_SSH2_MSG_USERAUTH_SUCCESS. For a failed login it's NET_SSH2_MSG_USERAUTH_FAILURE.
     *
     * @param string $path
     * @return string
     * @access private
     */
    protected function parse_path($path)
    {
        $scheme = parse_url($path, PHP_URL_SCHEME);
        if (isset($this->context)) {
            $options = stream_context_get_options($this->context);
        }
        if (isset($options[$scheme]['privkey']) && $options[$scheme]['privkey'] instanceof RSA) {
            stream_context_set_option($this->context, $scheme, 'privKey', $options[$scheme]['privkey']->getKeyObject());
        }
        if (isset($options[$scheme]['session']) && $options[$scheme]['session'] instanceof SFTP) {
            stream_context_set_option($this->context, $scheme, 'session', $options[$scheme]['session']->getSFTPObject());
        }
        if (isset($options[$scheme]['sftp']) && $options[$scheme]['sftp'] instanceof SFTP) {
            stream_context_set_option($this->context, $scheme, 'sftp', $options[$scheme]['sftp']->getSFTPObject());
        }
        return parent::parse_path($path);
    }

    /**
     *  __call() magic method
     *
     * @access public
     */
    public function __call($name, array $args)
    {
        try {
            return parent::__call($name, $args);
        } catch (\Exception $e) {
            user_error($e->getMessage());
        }
    }
}