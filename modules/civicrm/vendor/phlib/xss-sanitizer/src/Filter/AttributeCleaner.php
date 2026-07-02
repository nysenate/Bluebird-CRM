<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\AttributeFinder;
use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\TagFinder;
use Phlib\XssSanitizer\TagFinderInterface;

/**
 * @package Phlib\XssSanitizer
 */
class AttributeCleaner implements FilterInterface
{
    private TagFinderInterface $tagFinder;

    private AttributeFinder $attrFinder;

    private string $contentRegex;

    private FilterInterface $attributeContentCleaner;

    /**
     * @param string|string[]|null $tags
     */
    public function __construct(string $attribute, FilterInterface $attributeContentCleaner, $tags = null)
    {
        $this->tagFinder = $tags ? new TagFinder\ByTag($tags) : new TagFinder\ByAttribute($attribute);
        $this->attrFinder = new AttributeFinder($attribute);

        $this->contentRegex = $this->buildContentRegex();

        $this->attributeContentCleaner = $attributeContentCleaner;
    }

    /**
     * Given the tags and attribute to look for, will search for tags with that attribute containing potential XSS
     * exploits, and remove the attribute if found
     *
     * e.g. with $tags='a' and $attr='href'
     *     <a href="javascript:alert('XSS');">
     * should become
     *     <a >
     */
    public function filter(string $str): string
    {
        $str = $this->tagFinder->findTags($str, function ($fullTag, $attributes): string {
            return $this->cleanAttributes($fullTag, $attributes);
        });

        return $str;
    }

    /**
     * Search for the attribute in the tags, and clean it if found
     *
     * @param string $fullTag (e.g. '<a href="javascript:alert('XSS');">')
     * @param string $attributes (e.g. 'a href="javascript:alert('XSS');"')
     */
    private function cleanAttributes(string $fullTag, string $attributes): string
    {
        $replacement = $this->attrFinder->findAttributes($attributes, function ($fullAttribute, $attributeContents): string {
            return $this->cleanAttribute($fullAttribute, $attributeContents);
        });

        return str_ireplace($attributes, $replacement, $fullTag);
    }

    /**
     * Search the attribute content for any potential exploits, and return empty string
     *
     * @param string $fullAttribute (e.g. 'href="javascript:alert('XSS');"')
     * @param string $attributeContents (e.g. 'javascript:alert('XSS');')
     */
    private function cleanAttribute(string $fullAttribute, string $attributeContents): string
    {
        // decode entities, compact words etc.
        $cleanedContents = $this->attributeContentCleaner->filter($attributeContents);

        if (preg_match($this->contentRegex, $cleanedContents)) {
            return '';
        }

        return $fullAttribute;
    }

    private function buildContentRegex(): string
    {
        $dangerous = [
            'javascript:',
        ];

        return implode('', [
            '#',
            '(', implode('|', $dangerous), ')',
            '#i',
        ]);
    }
}
