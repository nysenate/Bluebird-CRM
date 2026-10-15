<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * @package Phlib\XssSanitizer
 */
class RemoveBlocks implements FilterInterface
{
    private string $searchRegex;

    /**
     * @param string|string[] $tags
     */
    public function __construct($tags)
    {
        $this->searchRegex = $this->initSearchRegex($tags);
    }

    /**
     * Filter tags out of the HTML by removing whole blocks (from opening tag to closing tag)
     *
     * This filter should be used in conjunction with @see \Phlib\XssSanitizer\Filter\EscapeTags to ensure that any
     * tags which are not picked up will be escaped
     *
     * e.g.
     *     <body><script type="text/javascript">alert('XSS');</script></body>
     * becomes
     *     <body></body>
     */
    public function filter(string $str): string
    {
        $str = preg_replace($this->searchRegex, '', $str);

        return $str;
    }

    /**
     * @param string|string[] $tags
     */
    private function initSearchRegex($tags): string
    {
        if (is_array($tags)) {
            $tags = '(?:' . implode('|', $tags) . ')';
        }
        return implode('', [
            '#',
            // open tag
            '<',
            '(', $tags, ')',
            '([^>]*?)',
            '>',
            // content
            '.*?',
            // closing tag
            '</',
            '\1',
            '([^>]*?)',
            '(>|$)',
            '#si',
        ]);
    }
}
