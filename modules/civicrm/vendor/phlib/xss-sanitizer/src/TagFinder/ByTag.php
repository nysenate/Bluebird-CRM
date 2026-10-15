<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\TagFinder;

use Phlib\XssSanitizer\TagFinderInterface;

/**
 * @package Phlib\XssSanitizer
 */
class ByTag implements TagFinderInterface
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
     * Given a full html string, finds the required tags by either tag name and calls the callback,
     * providing the full tag string and the attributes string
     *
     * The return value is used to replace the full tag string
     *
     * e.g. for an tag finder which is looking for an img tag
     * for the string
     *     '<body><img src="something"></body'
     * the callback will provide
     *     $fullTag:    '<img src="something">'
     *     $attributes: ' src="something"'
     * and the return from the callback would replace the $fullTag in the original string
     */
    public function findTags(string $str, callable $callback): string
    {
        return preg_replace_callback(
            $this->searchRegex,
            function ($matches) use ($callback) {
                return $callback($matches[0], $matches[1]);
            },
            $str
        );
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
            '#<',
            $tags,
            '[^a-z0-9>]+([^>]*)(?:>|$)',
            '#si',
        ]);
    }
}
