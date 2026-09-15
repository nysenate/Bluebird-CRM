<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\AttributeFinder;
use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\TagFinder;

/**
 * @package Phlib\XssSanitizer
 */
class MetaRefresh implements FilterInterface
{
    private TagFinder\ByTag $tagFinder;

    private AttributeFinder $attrFinder;

    private FilterInterface $attributeContentCleaner;

    public function __construct(FilterInterface $attributeContentCleaner)
    {
        $this->tagFinder = new TagFinder\ByTag('meta');
        $this->attrFinder = new AttributeFinder('http-equiv');

        $this->attributeContentCleaner = $attributeContentCleaner;
    }

    /**
     * Removes refresh meta tags
     * @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META
     *
     * e.g.
     *     <meta http-equiv="refresh" content="0;url=javascript:alert('XSS');">
     * would be removed
     */
    public function filter(string $str): string
    {
        $str = $this->tagFinder->findTags($str, function ($fullTag, $attributes): string {
            return $this->cleanTag($fullTag, $attributes);
        });
        return $str;
    }

    /**
     * Replaces the tag with an empty string if the 'http-equiv' is set to 'refresh'
     *
     * @param string $fullTag (e.g. '<meta http-equiv="refresh">')
     * @param string $attributes (e.g. 'meta http-equiv="refresh"')
     */
    private function cleanTag(string $fullTag, string $attributes): string
    {
        $isRefreshTag = false;

        $this->attrFinder->findAttributes($attributes, function ($full, $contents) use (&$isRefreshTag) {
            $cleanedContents = $this->attributeContentCleaner->filter($contents);
            if (preg_match('/refresh/i', $cleanedContents)) {
                $isRefreshTag = true;
            }
            return $full;
        });

        if ($isRefreshTag) {
            $fullTag = '';
        }
        return $fullTag;
    }
}
