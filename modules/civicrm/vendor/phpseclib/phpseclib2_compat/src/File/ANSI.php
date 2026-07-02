<?php

/**
 * Pure-PHP ANSI Decoder
 *
 * PHP version 5
 *
 * If you call read() in \phpseclib\Net\SSH2 you may get {@link http://en.wikipedia.org/wiki/ANSI_escape_code ANSI escape codes} back.
 * They'd look like chr(0x1B) . '[00m' or whatever (0x1B = ESC).  They tell a
 * {@link http://en.wikipedia.org/wiki/Terminal_emulator terminal emulator} how to format the characters, what
 * color to display them in, etc. \phpseclib\File\ANSI is a {@link http://en.wikipedia.org/wiki/VT100 VT100} terminal emulator.
 *
 * @category  File
 * @package   ANSI
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2012 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib\File;

/**
 * Pure-PHP ANSI Decoder
 *
 * @package ANSI
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class ANSI extends \phpseclib3\File\ANSI
{
}