<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter\AttributeContent;

use Phlib\XssSanitizer\FilterInterface;

/**
 * @package Phlib\XssSanitizer
 */
class DecodeEntities implements FilterInterface
{
    private string $entityRegex;

    public function __construct()
    {
        $this->entityRegex = $this->buildEntityRegex();
    }

    /**
     * Decode HTML entities in an attribute content string
     *
     * e.g.
     *     java&#115;cript:alert('XSS');
     * becomes
     *     javascript:alert('XSS');
     */
    public function filter(string $str): string
    {
        $str = preg_replace_callback(
            $this->entityRegex,
            function ($matches): string {
                if ($matches[1]) {
                    $entity = "&#{$matches[1]};";
                } else {
                    $entity = "&#x{$matches[2]};";
                }
                return mb_convert_encoding($entity, 'UTF-8', 'HTML-ENTITIES');
            },
            $str
        );
        return $str;
    }

    private function buildEntityRegex(): string
    {
        return implode('', [
            '/',
            '&#',
            '(?:',
            // decimal
            '(?:0*)', // ignore zero padding
            '([0-9]+)',
            '|',
            // hexadecimal
            'x(?:0*)', // ignore zero padding
            '([0-9a-f]+)',
            ')',
            '(;)?',
            '/i',
        ]);
    }
}
