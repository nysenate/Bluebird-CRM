<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * @package Phlib\XssSanitizer
 */
class EscapeTags implements FilterInterface
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
     * Filter tags by html encoding the opening angle bracket
     *
     * e.g.
     *     <script type="text/javascript">alert('XSS');</script>
     * becomes
     *     &lt;script type="text/javascript">alert('XSS');&lt;/script>
     */
    public function filter(string $str): string
    {
        $str = preg_replace($this->searchRegex, '&lt;\1', $str);

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
            '<',
            '(/?', $tags, ')',
            '#si',
        ]);
    }
}
