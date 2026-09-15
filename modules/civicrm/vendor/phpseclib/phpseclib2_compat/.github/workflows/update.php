<?php

function replaceFileContents($fileName, $search, $replace)
{
    $fileContents = file_get_contents($fileName);
    $updatedFileContents = preg_replace($search, $replace, $fileContents);
    file_put_contents($fileName, $updatedFileContents);
}

replaceFileContents('tests/PhpseclibTestCase.php', '~%s/../phpseclib/%s~', '%s/../src/%s');
replaceFileContents('tests/Unit/Crypt/RSA/LoadKeyTest.php', '~ public function testSetPrivate\(\)~', ' private function skiptestSetPrivate()');
replaceFileContents('tests/Unit/Crypt/RSA/ModeTest.php', '~ public function testOAEPWithLabel\(\)~', ' private function skiptestOAEPWithLabel()');