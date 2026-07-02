<?php

/**
 * Pure-PHP implementation of SFTP.
 *
 * PHP version 5
 *
 * Currently only supports SFTPv2 and v3, which, according to wikipedia.org, "is the most widely used version,
 * implemented by the popular OpenSSH SFTP server".  If you want SFTPv4/5/6 support, provide me with access
 * to an SFTPv4/5/6 server.
 *
 * The API for this library is modeled after the API from PHP's {@link http://php.net/book.ftp FTP extension}.
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $sftp = new \phpseclib\Net\SFTP('www.domain.tld');
 *    if (!$sftp->login('username', 'password')) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $sftp->pwd() . "\r\n";
 *    $sftp->put('filename.ext', 'hello, world!');
 *    print_r($sftp->nlist());
 * ?>
 * </code>
 *
 * @category  Net
 * @package   SFTP
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2009 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\Net;

use phpseclib\Crypt\RSA;

/**
 * Pure-PHP implementation of SFTP.
 *
 * @package SFTP
 * @method void disableStatCache()
 * @method void enableStatCache()
 * @method void clearStatCache()
 * @method void enablePathCanonicalization()
 * @method void disablePathCanonicalization()
 * @method void enableArbitraryLengthPackets()
 * @method void disableArbitraryLengthPackets()
 * @method string|false pwd()
 * @method string|false realpath(string $path)
 * @method bool chdir(string $dir)
 * @method string[]|false nlist(string $dir = '.', bool $recursive = false)
 * @method mixed[]|false rawlist(string $dir = '.', bool $recursive = false)
 * @method void setListOrder(mixed ...$args)
 * @method mixed[]|false stat(string $filename)
 * @method mixed[]|false lstat(string $filename)
 * @method bool truncate(string $filename, int $new_size)
 * @method bool touch(string $filename, int $time = null, int $atime = null)
 * @method bool chown(string $filename, int|string $uid, bool $recursive = false)
 * @method bool chgrp(string $filename, int|string $gid, bool $recursive = false)
 * @method bool chmod(int $mode, string $filename, bool $recursive = false)
 * @method mixed readlink(string $link)
 * @method bool symlink(string $target, string $link)
 * @method bool mkdir(string $dir, int $mode = -1, bool $recursive = false)
 * @method bool rmdir(string $dir)
 * @method bool put(string $remote_file, string $data, int $mode = SFTP::SOURCE_STRING, int $start = -1, int $local_start = -1, ?callable $progressCallback = null)
 * @method string|bool get(string $remote_file, string $local_file = false, int $offset = 0, int $length = -1, ?callable $progressCallback = null)
 * @method bool delete(string $path, bool $recursive = true)
 * @method bool file_exists(string $path)
 * @method bool is_dir(string $path)
 * @method bool is_file(string $path)
 * @method bool is_link(string $path)
 * @method bool is_readable(string $path)
 * @method bool is_writable(string $path)
 * @method bool is_writeable(string $path)
 * @method int|float|false fileatime(string $path)
 * @method int|float|false filemtime(string $path)
 * @method int|false fileperms(string $path)
 * @method int|false fileowner(string $path)
 * @method int|false filegroup(string $path)
 * @method int|float|false filesize(string $path)
 * @method string|false filetype(string $path)
 * @method bool rename(string $oldname, string $newname)
 * @method string[]|string getSFTPLog()
 * @method string[] getSFTPErrors()
 * @method string getLastSFTPError()
 * @method mixed[]|false getSupportedVersions()
 * @method int|false getNegotiatedVersion()
 * @method void setPreferredVersion(int $version)
 * @method void enableDatePreservation()
 * @method void disableDatePreservation()
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class SFTP
{
    /**#@+
     * @access public
     * @see \phpseclib\Net\SFTP::put()
    */
    /**
     * Reads data from a local file.
     */
    const SOURCE_LOCAL_FILE = 1;
    /**
     * Reads data from a string.
     */
    // this value isn't really used anymore but i'm keeping it reserved for historical reasons
    const SOURCE_STRING = 2;
    /**
     * Reads data from callback:
     * function callback($length) returns string to proceed, null for EOF
     */
    const SOURCE_CALLBACK = 16;
    /**
     * Resumes an upload
     */
    const RESUME = 4;
    /**
     * Append a local file to an already existing remote file
     */
    const RESUME_START = 8;
    /**#@-*/

    /**
     * The SFTP object
     *
     * @var \phpseclib3\File\SFTP
     * @access private
     */
    private $sftp = null;

    /**
     * Default Constructor.
     *
     * Connects to an SFTP server
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return \phpseclib\Net\SFTP
     * @access public
     */
    function __construct($host, $port = 22, $timeout = 10)
    {
        $this->sftp = new \phpseclib3\Net\SFTP($host, $port, $timeout);
    }

    /**
     * Login
     *
     * The $password parameter can be a plaintext password, a \phpseclib3\Crypt\RSA object or an array
     *
     * @param string $username
     * @param $args[] param mixed $password
     * @return bool
     * @see self::_login()
     * @access public
     */
    public function login($username, ...$args)
    {
        foreach ($args as &$arg) {
            if ($arg instanceof RSA) {
                $arg = $arg->getKeyObject();
                if (!$arg instanceof \phpseclib3\Crypt\Common\PrivateKey) {
                    return false;
                }
            }
        }

        try {
            return $this->sftp->login($username, ...$args);
        } catch (\Exception $e) {
            user_error($e->getMessage());
            return false;
        }
    }

    /**
     * Parse Attributes
     *
     * See '7.  File Attributes' of draft-ietf-secsh-filexfer-13 for more info.
     *
     * @param string $response
     * @return array
     * @access private
     */
    protected function parseAttributes(&$response)
    {
        $r = $this->sftp->parseAttributes($response);
        if (isset($r['mode'])) {
            $r['permissions'] = $r['mode'];
        }
        return $r;
    }

    /**
     * Defines how nlist() and rawlist() will be sorted - if at all.
     *
     * If sorting is enabled directories and files will be sorted independently with
     * directories appearing before files in the resultant array that is returned.
     *
     * Any parameter returned by stat is a valid sort parameter for this function.
     * Filename comparisons are case insensitive.
     *
     * Examples:
     *
     * $sftp->setListOrder('filename', SORT_ASC);
     * $sftp->setListOrder('size', SORT_DESC, 'filename', SORT_ASC);
     * $sftp->setListOrder(true);
     *    Separates directories from files but doesn't do any sorting beyond that
     * $sftp->setListOrder();
     *    Don't do any sort of sorting
     *
     * @param $args[]
     * @access public
     */
    public function setListOrder(...$args)
    {
        $sortOptions = [];
        if (empty($args)) {
            return;
        }
        $len = count($args) & 0x7FFFFFFE;
        for ($i = 0; $i < $len; $i+=2) {
            if ($args[$i] == 'permissions') {
                $args[$i] = 'mode';
            }
            $sortOptions[$args[$i]] = $args[$i + 1];
        }
        $this->sftp->setListOrder(...$args);
    }

    /**
     * Returns the file size, in bytes, or false, on failure
     *
     * Files larger than 4GB will show up as being exactly 4GB.
     *
     * @param string $filename
     * @return mixed
     * @access public
     */
    public function size($filename)
    {
        return $this->sftp->filesize($filename);
    }

    /**
     * Returns a public key object
     *
     * @access public
     * @return SFTP|false
     */
    public function getSFTPObject()
    {
        return $this->sftp;
    }

    /**
     *  __call() magic method
     *
     * @access public
     */
    public function __call($name, $args)
    {
        try {
            return $this->sftp->$name(...$args);
        } catch (\Exception $e) {
            user_error($e->getMessage());
        }
    }
}