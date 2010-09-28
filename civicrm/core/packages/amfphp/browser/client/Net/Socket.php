<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Stig Bakken <ssb@php.net>                                   |
// |          Chuck Hagenbuch <chuck@horde.org>                           |
// +----------------------------------------------------------------------+
//
// $Id: Socket.php,v 1.24 2005/02/03 20:40:16 chagenbu Exp $


define('NET_SOCKET_READ',  1);
define('NET_SOCKET_WRITE', 2);
define('NET_SOCKET_ERROR', 3);


class Net_Socket {

	var $fp = null;
	var $blocking = true;
	var $persistent = false;
	var $addr = '';
	var $port = 0;
	var $timeout = false;
	var $lineLength = 2048;

	function connect($addr, $port = 0, $persistent = null, $timeout = null, $options = null)
	{
		if (is_resource($this->fp)) {
			@fclose($this->fp);
			$this->fp = null;
		}

		if (!$addr) {
			return $this->raiseError('$addr cannot be empty');
		} elseif (strspn($addr, '.0123456789') == strlen($addr) ||
				  strstr($addr, '/') !== false) {
			$this->addr = $addr;
		} else {
			$this->addr = @gethostbyname($addr);
		}

		$this->port = $port % 65536;

		if ($persistent !== null) {
			$this->persistent = $persistent;
		}

		if ($timeout !== null) {
			$this->timeout = $timeout;
		}

		$openfunc = $this->persistent ? 'pfsockopen' : 'fsockopen';
		$errno = 0;
		$errstr = '';
		if ($options && function_exists('stream_context_create')) {
			if ($this->timeout) {
				$timeout = $this->timeout;
			} else {
				$timeout = 0;
			}
			$context = stream_context_create($options);
			$fp = @$openfunc($this->addr, $this->port, $errno, $errstr, $timeout, $context);
		} else {
			if ($this->timeout) {
				$fp = @$openfunc($this->addr, $this->port, $errno, $errstr, $this->timeout);
			} else {
				$fp = @$openfunc($this->addr, $this->port, $errno, $errstr);
			}
		}

		if (!$fp) {
			return $this->raiseError($errstr, $errno);
		}

		$this->fp = $fp;

		return $this->setBlocking($this->blocking);
	}

	
	function disconnect()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		@fclose($this->fp);
		$this->fp = null;
		return true;
	}

	
	function isBlocking()
	{
		return $this->blocking;
	}

	
	function setBlocking($mode)
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$this->blocking = $mode;
		socket_set_blocking($this->fp, $this->blocking);
		return true;
	}

	
	function setTimeout($seconds, $microseconds)
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		return socket_set_timeout($this->fp, $seconds, $microseconds);
	}

	
	function getStatus()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		return socket_get_status($this->fp);
	}

	
	function gets($size)
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		return @fgets($this->fp, $size);
	}

	
	function read($size)
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		return @fread($this->fp, $size);
	}

	
	function write($data, $blocksize = null)
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		if (is_null($blocksize) && !OS_WINDOWS) {
			return fwrite($this->fp, $data);
		} else {
			if (is_null($blocksize)) {
				$blocksize = 1024;
			}

			$pos = 0;
			$size = strlen($data);
			while ($pos < $size) {
				$written = @fwrite($this->fp, substr($data, $pos, $blocksize));
				if ($written === false) {
					return false;
				}
				$pos += $written;
			}

			return $pos;
		}
	}

	
	function writeLine($data)
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		return fwrite($this->fp, $data . "\r\n");
	}

	
	function eof()
	{
		return (is_resource($this->fp) && feof($this->fp));
	}

	
	function readByte()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		return ord(@fread($this->fp, 1));
	}

	
	function readWord()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$buf = @fread($this->fp, 2);
		return (ord($buf[0]) + (ord($buf[1]) << 8));
	}

	
	function readInt()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$buf = @fread($this->fp, 4);
		return (ord($buf[0]) + (ord($buf[1]) << 8) +
				(ord($buf[2]) << 16) + (ord($buf[3]) << 24));
	}

	
	function readString()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$string = '';
		while (($char = @fread($this->fp, 1)) != "\x00")  {
			$string .= $char;
		}
		return $string;
	}

	
	function readIPAddress()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$buf = @fread($this->fp, 4);
		return sprintf("%s.%s.%s.%s", ord($buf[0]), ord($buf[1]),
					   ord($buf[2]), ord($buf[3]));
	}

	
	function readLine()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$line = '';
		$timeout = time() + $this->timeout;
		while (!feof($this->fp) && (!$this->timeout || time() < $timeout)) {
			$line .= @fgets($this->fp, $this->lineLength);
			if (substr($line, -1) == "\n") {
				return rtrim($line, "\r\n");
			}
		}
		return $line;
	}

	
	function readAll()
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$data = '';
		while (!feof($this->fp)) {
			$data .= @fread($this->fp, $this->lineLength);
		}
		return $data;
	}

	
	function select($state, $tv_sec, $tv_usec = 0)
	{
		if (!is_resource($this->fp)) {
			return $this->raiseError('not connected');
		}

		$read = null;
		$write = null;
		$except = null;
		if ($state & NET_SOCKET_READ) {
			$read[] = $this->fp;
		}
		if ($state & NET_SOCKET_WRITE) {
			$write[] = $this->fp;
		}
		if ($state & NET_SOCKET_ERROR) {
			$except[] = $this->fp;
		}
		if (false === ($sr = stream_select($read, $write, $except, $tv_sec, $tv_usec))) {
			return false;
		}

		$result = 0;
		if (count($read)) {
			$result |= NET_SOCKET_READ;
		}
		if (count($write)) {
			$result |= NET_SOCKET_WRITE;
		}
		if (count($except)) {
			$result |= NET_SOCKET_ERROR;
		}
		return $result;
	}
	
	function raiseError($str)
	{
		trigger_error($str, E_USER_ERROR);
	}

}
 