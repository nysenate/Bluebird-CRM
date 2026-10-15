<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter\AttributeContent;

use Phlib\XssSanitizer\FilterInterface;

/**
 * @package Phlib\XssSanitizer
 */
class DecodeUtf8 implements FilterInterface
{
    /**
     * Decode UTF-8 encoded characters in an attribute content string
     *
     * e.g.
     *     \u006Aavascript:alert('XSS');
     * becomes
     *     javascript:alert('XSS');
     */
    public function filter(string $str): string
    {
        $str = preg_replace_callback(
            '#\\\\u([0-9a-f]{4})#i',
            function ($matches): string {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
            },
            $str
        );

        return $str;
    }
}
