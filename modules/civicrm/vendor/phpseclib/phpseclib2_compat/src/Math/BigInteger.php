<?php

/**
 * Pure-PHP arbitrary precision integer arithmetic library.
 *
 * Supports base-2, base-10, base-16, and base-256 numbers.  Uses the GMP or BCMath extensions, if available,
 * and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * {@internal (all DocBlock comments regarding implementation - such as the one that follows - refer to the
 * {@link self::MODE_INTERNAL self::MODE_INTERNAL} mode)
 *
 * BigInteger uses base-2**26 to perform operations such as multiplication and division and
 * base-2**52 (ie. two base 2**26 digits) to perform addition and subtraction.  Because the largest possible
 * value when multiplying two base-2**26 numbers together is a base-2**52 number, double precision floating
 * point numbers - numbers that should be supported on most hardware and whose significand is 53 bits - are
 * used.  As a consequence, bitwise operators such as >> and << cannot be used, nor can the modulo operator %,
 * which only supports integers.  Although this fact will slow this library down, the fact that such a high
 * base is being used should more than compensate.
 *
 * Numbers are stored in {@link http://en.wikipedia.org/wiki/Endianness little endian} format.  ie.
 * (new \phpseclib\Math\BigInteger(pow(2, 26)))->value = array(0, 1)
 *
 * Useful resources are as follows:
 *
 *  - {@link http://www.cacr.math.uwaterloo.ca/hac/about/chap14.pdf Handbook of Applied Cryptography (HAC)}
 *  - {@link http://math.libtomcrypt.com/files/tommath.pdf Multi-Precision Math (MPM)}
 *  - Java's BigInteger classes.  See /j2se/src/share/classes/java/math in jdk-1_5_0-src-jrl.zip
 *
 * Here's an example of how to use this library:
 * <code>
 * <?php
 *    $a = new \phpseclib\Math\BigInteger(2);
 *    $b = new \phpseclib\Math\BigInteger(3);
 *
 *    $c = $a->add($b);
 *
 *    echo $c->toString(); // outputs 5
 * ?>
 * </code>
 *
 * @category  Math
 * @package   BigInteger
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2006 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib\Math;

use phpseclib3\Math\BigInteger as BigInteger2;

/**
 * Pure-PHP arbitrary precision integer arithmetic library. Supports base-2, base-10, base-16, and base-256
 * numbers.
 *
 * @package BigInteger
 * @method string toString()
 * @method string toBytes()
 * @method string toHex()
 * @method string toBits()
 * @method BigInteger add(BigInteger $y)
 * @method BigInteger subtract(BigInteger $y)
 * @method BigInteger multiply(BigInteger $x)
 * @method array{BigInteger, BigInteger} divide(BigInteger $x)
 * @method BigInteger modInverse(BigInteger $n)
 * @method {'gcd': BigInteger, 'x': BigInteger, 'y': BigInteger} extendedGCD(BigInteger $n)
 * @method BigInteger gcd(BigInteger $n)
 * @method BigInteger abs()
 * @method void setPrecision(int $bits)
 * @method int|bool getPrecision()
 * @method BigInteger powMod(BigInteger $e, BigInteger $n)
 * @method BigInteger modPow(BigInteger $e, BigInteger $n)
 * @method int compare(BigInteger $y)
 * @method bool equals(BigInteger $x)
 * @method BigInteger bitwise_not()
 * @method BigInteger bitwise_and(BigInteger $x)
 * @method BigInteger bitwise_or(BigInteger $x)
 * @method BigInteger bitwise_rightShift($shift)
 * @method BigInteger bitwise_leftShift($shift)
 * @method BigInteger bitwise_leftRotate($shift)
 * @method BigInteger bitwise_rightRotate($shift)
 * @method {'min': BigInteger, 'max': BigInteger} minMaxBits($bits)
 * @method int getLength()
 * @method int getLengthInBytes()
 * @method bool isPrime(int|bool $t = false)
 * @method BigInteger root(int $n = 2)
 * @method BigInteger pow(BigInteger $n)
 * @method static BigInteger min(BigInteger ...$nums)
 * @method static BigInteger max(BigInteger ...$nums)
 * @method bool between(BigInteger $min, BigInteger $max)
 * @method bool isOdd()
 * @method bool testBit(int $x)
 * @method bool isNegative()
 * @method BigInteger negate()
 * @method callable createRecurringModuloFunction()
 * @method BigInteger[] bitwise_split(int $split)
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class BigInteger
{
    /**#@+
     * Array constants
     *
     * Rather than create a thousands and thousands of new BigInteger objects in repeated function calls to add() and
     * multiply() or whatever, we'll just work directly on arrays, taking them in as parameters and returning them.
     *
     * @access private
     */
    /**
     * $result[self::VALUE] contains the value.
     */
    const VALUE = 0;
    /**
     * $result[self::SIGN] contains the sign.
     */
    const SIGN = 1;
    /**#@-*/

    /**#@+
     * @access private
     * @see BigInteger::_montgomery()
     * @see BigInteger::_barrett()
     */
    /**
     * Cache constants
     *
     * $cache[self::VARIABLE] tells us whether or not the cached data is still valid.
     */
    const VARIABLE = 0;
    /**
     * $cache[self::DATA] contains the cached data.
     */
    const DATA = 1;
    /**#@-*/

    /**#@+
     * Mode constants.
     *
     * @access private
     * @see BigInteger::__construct()
     */
    /**
     * To use the pure-PHP implementation
     */
    const MODE_INTERNAL = 1;
    /**
     * To use the BCMath library
     *
     * (if enabled; otherwise, the internal implementation will be used)
     */
    const MODE_BCMATH = 2;
    /**
     * To use the GMP library
     *
     * (if present; otherwise, either the BCMath or the internal implementation will be used)
     */
    const MODE_GMP = 3;
    /**#@-*/

    /**
     * The BigInteger object
     *
     * @var \phpseclib3\Math\BigInteger
     * @access private
     */
    private $bigint;

    /**
     * Converts base-2, base-10, base-16, and binary strings (base-256) to BigIntegers.
     *
     * If the second parameter - $base - is negative, then it will be assumed that the number's are encoded using
     * two's compliment.  The sole exception to this is -10, which is treated the same as 10 is.
     *
     * Here's an example:
     * <code>
     * <?php
     *    $a = new \phpseclib\Math\BigInteger('0x32', 16); // 50 in base-16
     *
     *    echo $a->toString(); // outputs 50
     * ?>
     * </code>
     *
     * @param $x base-10 number or base-$base number if $base set.
     * @param int $base
     * @return \phpseclib\Math\BigInteger
     * @access public
     */
    public function __construct($x = 0, $base = 10)
    {
        $this->bigint = new BigInteger2($x, $base);
    }

    /**
     *  __call() magic method
     *
     * @access public
     */
    public function __call($name, $args)
    {
        foreach ($args as &$arg) {
            if ($arg instanceof BigInteger) {
                $arg = $arg->bigint;
            }
        }
        $result = $this->bigint->$name(...$args);
        if (!$result instanceof BigInteger2) {
            return $result;
        }

        $temp = new static;
        $temp->bigint = $result;

        return $temp;
    }

    /**
     *  __toString() magic method
     *
     * Will be called, automatically, if you're supporting just PHP5.  If you're supporting PHP4, you'll need to call
     * toString().
     *
     * @access public
     * @internal Implemented per a suggestion by Techie-Michael - thanks!
     */
    public function __toString()
    {
        return $this->bigint->__toString();
    }

    /**
     *  __debugInfo() magic method
     *
     * Will be called, automatically, when print_r() or var_dump() are called
     *
     * @access public
     */
    public function __debugInfo()
    {
        return $this->bigint->__debugInfo();
    }

    /**
     * Generate a random number
     *
     * Returns a random number between $min and $max where $min and $max
     * can be defined using one of the two methods:
     *
     * $min->random($max)
     * $max->random($min)
     *
     * @param \phpseclib\Math\BigInteger $arg1
     * @param \phpseclib\Math\BigInteger $arg2
     * @return \phpseclib\Math\BigInteger
     * @access public
     * @internal The API for creating random numbers used to be $a->random($min, $max), where $a was a BigInteger object.
     *           That method is still supported for BC purposes.
     */
    public function random($arg1, $arg2 = false)
    {
        $temp = new static;
        $temp->bigint = BigInteger2::randomRange(
            $arg1->bigint,
            $arg2 instanceof BigInteger ? $arg2->bigint : $this->bigint
        );
        return $temp;
    }

    /**
     * Generate a random prime number.
     *
     * If there's not a prime within the given range, false will be returned.
     * If more than $timeout seconds have elapsed, give up and return false.
     *
     * @param \phpseclib\Math\BigInteger $arg1
     * @param \phpseclib\Math\BigInteger $arg2
     * @return Math_BigInteger|false
     * @access public
     * @internal See {@link http://www.cacr.math.uwaterloo.ca/hac/about/chap4.pdf#page=15 HAC 4.44}.
     */
    public function randomPrime($arg1, $arg2 = false)
    {
        $temp = new static;
        $temp->bigint = BigInteger2::randomRange(
            $arg1->bigint,
            $arg2 instanceof BigInteger ? $arg2->bigint : $this->bigint
        );
        return $temp;
    }

    /**
     * Logical Exclusive-Or
     *
     * See https://github.com/phpseclib/phpseclib/issues/1245 for more context
     *
     * @param \phpseclib\Math\BigInteger $x
     * @access public
     * @return \phpseclib\Math\BigInteger
     */
    public function bitwise_xor($x)
    {
        $temp = new static;
        $temp->bigint = $this->bigint->abs()->bitwise_xor($x->bigint->abs());
        return $temp;
    }
}