# phpseclib2_compat

[![CI Status](https://github.com/phpseclib/phpseclib2_compat/actions/workflows/ci.yml/badge.svg?branch=1.0&event=push "CI Status")](https://github.com/phpseclib/phpseclib2_compat/actions/workflows/ci.yml?query=branch%3A1.0)

phpseclib 2.0 polyfill built with phpseclib 3.0

## Overview

phpseclib 3.0 breaks backwards compatability with phpseclib 2.0. Most notably, public keys work completely differently. So let's say you wanted to use phpseclib 3.0 whilst some of your other dependencies still use phpseclib 2.0. What would you do in that instance?

That's where phpseclib2_compat comes into play. Require phpseclib/phpseclib:~3.0 and phpseclib/phpseclib2_compat:~1.0 and your dependencies will magically start using phpseclib 3.0 even if they don't know it.

Using phpseclib2_compat will actually bring a few enhancements to your dependencies. For example, while phpseclib 2.0 only supports RSA keys phpseclib2_compat sports support for ECDSA / DSA / Ed25519 / Ed449 keys.

Consider this code sample:

```php
use phpseclib\Crypt\RSA;

$rsa = new RSA;
$rsa->loadKey('ecdsa private key');

$ssh = new SSH2('website.com');
$ssh->login('username', $rsa);
```
That'll work with phpseclib2_compat, even with an ECDSA private key, whereas in phpseclib 2.0 it would not work.

SSH1 and SCP are not supported but those were likely never frequently used anyway.

## Using the old cipher suite

phpseclib 3.0 uses a different cipher suite (an expanded one) than 2.0. If this causes you issues you can use the 2.0 ciphersuite by doing this prior to calling `$ssh->login()`:

```php
$methods = [
    'crypt' => array_intersect([
        'arcfour256',
        'arcfour128',
        'aes128-ctr',
        'aes192-ctr',
        'aes256-ctr',
        'twofish128-ctr',
        'twofish192-ctr',
        'twofish256-ctr',
        'aes128-cbc',
        'aes192-cbc',
        'aes256-cbc',
        'twofish128-cbc',
        'twofish192-cbc',
        'twofish256-cbc',
        'twofish-cbc',
        'blowfish-ctr',
        'blowfish-cbc',
        '3des-ctr',
        '3des-cbc'
    ], $ssh->getSupportedEncryptionAlgorithms()),
    'mac' => [
        'hmac-sha2-256',
        'hmac-sha1-96',
        'hmac-sha1',
        'hmac-md5-96',
        'hmac-md5'
    ],
    'comp' => ['none']
];

$ssh->setPreferredAlgorithms([
    'kex' => [
        'curve25519-sha256@libssh.org',
        'diffie-hellman-group-exchange-sha256',
        'diffie-hellman-group-exchange-sha1',
        'diffie-hellman-group14-sha1',
        'diffie-hellman-group14-sha256'
    ],
    'hostkey' => [
        'rsa-sha2-256',
        'rsa-sha2-512',
        'ssh-rsa',
        'ssh-dss'
    ],
    'client_to_server' => $methods,
    'server_to_client' => $methods
]);
```

## Installation

With [Composer](https://getcomposer.org/):

```
composer require phpseclib/phpseclib2_compat:~1.0
```
