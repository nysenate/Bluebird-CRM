<?php

/**
 * Pure-PHP implementation of SSHv2.
 *
 * PHP version 5
 *
 * Here are some examples of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $ssh = new \phpseclib\Net\SSH2('www.domain.tld');
 *    if (!$ssh->login('username', 'password')) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $ssh->exec('pwd');
 *    echo $ssh->exec('ls -la');
 * ?>
 * </code>
 *
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $key = new \phpseclib\Crypt\RSA();
 *    //$key->setPassword('whatever');
 *    $key->loadKey(file_get_contents('privatekey'));
 *
 *    $ssh = new \phpseclib\Net\SSH2('www.domain.tld');
 *    if (!$ssh->login('username', $key)) {
 *        exit('Login Failed');
 *    }
 *
 *    echo $ssh->read('username@username:~$');
 *    $ssh->write("ls -la\n");
 *    echo $ssh->read('username@username:~$');
 * ?>
 * </code>
 *
 * @category  Net
 * @package   SSH2
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */


namespace phpseclib\Net;

use phpseclib\Crypt\RSA;

/**
 * Pure-PHP implementation of SSHv2.
 *
 * @package SSHv2
 * @method static void setCryptoEngine(string $engine)
 * @method void sendIdentificationStringFirst()
 * @method void sendIdentificationStringLast()
 * @method void sendKEXINITFirst()
 * @method void sendKEXINITLast()
 * @method int|float getTimeout()
 * @method void setTimeout(int|float $timeout)
 * @method void setKeepAlive(int|float $interval)
 * @method string getStdError()
 * @method string|bool exec(string $command, callable ?$callback = null)
 * @method bool requestAgentForwarding()
 * @method string|bool|null read(string $expect = '', int $mode = SSH2::READ_SIMPLE)
 * @method void write(string $cmd)
 * @method bool startSubsystem(string $subsystem)
 * @method bool stopSubsystem()
 * @method void reset()
 * @method bool isTimeout()
 * @method void disconnect()
 * @method bool isConnected()
 * @method bool isAuthenticated()
 * @method bool ping()
 * @method void enableQuietMode()
 * @method void disableQuietMode()
 * @method bool isQuietModeEnabled()
 * @method void enablePTY()
 * @method void disablePTY()
 * @method bool isPTYEnabled()
 * @method array|false|string getLog()
 * @method string[] getErrors()
 * @method ?string getLastError()
 * @method string|false getServerIdentification()
 * @method mixed[] getServerAlgorithms()
 * @method static string[] getSupportedKEXAlgorithms()
 * @method static string[] getSupportedHostKeyAlgorithms()
 * @method static string[] getSupportedEncryptionAlgorithms()
 * @method static string[] getSupportedMACAlgorithms()
 * @method static string[] getSupportedCompressionAlgorithms()
 * @method mixed[] getAlgorithmsNegotiated()
 * @method void setTerminal(string $term)
 * @method void setPreferredAlgorithms(mixed[] $methods)
 * @method string getBannerMessage()
 * @method string|false getServerPublicHostKey()
 * @method false|int getExitStatus()
 * @method int getWindowColumns()
 * @method int getWindowRows()
 * @method setWindowColumns(int $value)
 * @method setWindowRows(int $value)
 * @method setWindowSize(int $columns = 80, int $rows = 24)
 * @method string getResourceId()
 * @method static bool|SSH2 getConnectionByResourceId(string $id)
 * @method static array<string, SSH2> getConnections()
 * @method ?mixed[] getAuthMethodsToContinue()
 * @method void enableSmartMFA()
 * @method void disableSmartMFA()
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class SSH2
{
    /**#@+
     * @access public
     * @see \phpseclib\Net\SSH2::getLog()
    */
    /**
     * Returns the message numbers
     */
    const LOG_SIMPLE = 1;
    /**
     * Returns the message content
     */
    const LOG_COMPLEX = 2;
    /**
     * Outputs the content real-time
     */
    const LOG_REALTIME = 3;
    /**
     * Dumps the content real-time to a file
     */
    const LOG_REALTIME_FILE = 4;
    /**
     * Make sure that the log never gets larger than this
     */
    const LOG_MAX_SIZE = 1048576; // 1024 * 1024
    /**#@-*/

    /**#@+
     * @access public
     * @see \phpseclib\Net\SSH2::read()
    */
    /**
     * Returns when a string matching $expect exactly is found
     */
    const READ_SIMPLE = 1;
    /**
     * Returns when a string matching the regular expression $expect is found
     */
    const READ_REGEX = 2;
    /**
     * Returns whenever a data packet is received.
     *
     * Some data packets may only contain a single character so it may be necessary
     * to call read() multiple times when using this option
     */
    const READ_NEXT = 3;
    /**#@-*/

    /**
     * The SSH2 object
     *
     * @var \phpseclib3\File\SSH2
     * @access private
     */
    private $ssh;

    /**
     * Default Constructor.
     *
     * $host can either be a string, representing the host, or a stream resource.
     *
     * @param mixed $host
     * @param int $port
     * @param int $timeout
     * @see self::login()
     * @return \phpseclib\Net\SSH2
     * @access public
     */
    function __construct($host, $port = 22, $timeout = 10)
    {
        $this->ssh = new \phpseclib3\Net\SSH2($host, $port, $timeout);
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
                if (!$arg) {
                    return false;
                }
            }
        }

        try {
            return $this->ssh->login($username, ...$args);
        } catch (\Exception $e) {
            user_error($e->getMessage());
            return false;
        }
    }

    /**
     *  __call() magic method
     *
     * @access public
     */
    public function __call($name, $args)
    {
        try {
            return $this->ssh->$name(...$args);
        } catch (\Exception $e) {
            user_error($e->getMessage());
        }
    }
}