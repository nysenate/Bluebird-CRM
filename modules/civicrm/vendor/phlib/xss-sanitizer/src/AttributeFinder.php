<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer;

/**
 * @package Phlib\XssSanitizer
 */
class AttributeFinder
{
    private array $attributes;

    private string $optimisticSearchRegex;

    private string $pessimisticSearchRegex;

    /**
     * @param string|string[] $attributes
     */
    public function __construct($attributes)
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }
        $this->attributes = $attributes;
        $this->optimisticSearchRegex = $this->initOptimisticSearchRegex();
        $this->pessimisticSearchRegex = $this->initPessimisticSearchRegex($attributes);
    }

    /**
     * Given the attributes string of an element, finds the required attribute(s) and calls the callback, providing the
     * full attribute string and the content (value) of the attribute
     *
     * The return value is used to replace the full attribute string
     *
     * e.g. for an attribute finder which is looking for the 'href' attribute
     * for the string
     *     'a href="something" id="link"'
     * the callback will provide
     *     $fullAttribute:    'href="something"'
     *     $attributeContent: 'something'
     * and the return from the callback would replace the $fullAttribute in the original string
     */
    public function findAttributes(string $attributes, callable $callback): string
    {
        $filtered = [];

        $this->findAttributesOptimistic($attributes, $callback, $filtered);

        $this->findAttributesPessimistic($attributes, $callback, $filtered);

        return implode('', $filtered);
    }

    /**
     * Find attributes hoping for well-formed and valid HTML
     *
     * This should prevent a certain number of false positives by nicely handling attributes which are
     * well formed and syntactically good
     *
     * This allows us to ignore cases where an attribute name appears in the context of an attribute value
     * when we know that the attribute is well formed
     */
    private function findAttributesOptimistic(string &$attributes, callable $callback, array &$filtered): void
    {
        while (preg_match($this->optimisticSearchRegex, $attributes, $matches)) {
            $attributes = substr($attributes, strlen($matches[0]));
            if (in_array(strtolower($matches[3]), $this->attributes, true)) {
                $replacement = $callback($matches[2], $matches[5]);
            } else {
                $replacement = $matches[2];
            }
            $filtered[] = $matches[1]; // whitespace
            $filtered[] = $replacement;
        }
    }

    private function initOptimisticSearchRegex(): string
    {
        return implode('', [
            '#',
            '^(\s*)',        // group 1 (whitespace)
            '(',             // group 2 (full attribute)
            '([a-z]+)',  // group 3 (attribute name)
            '=',
            '(["\'])',   // group 4 (quote)
            '(.*?)',     // group 5 (attribute value)
            '\4',
            ')',
            '#si',
        ]);
    }

    /**
     * Find attributes where the attribute syntax may not be well formed
     *
     * This acts as a fallback when the optimistic search is not able to parse the attributes
     *
     * Here, we aren't too bothered about false positives; we want to make sure we catch all and any possibilities
     * of the attribute appearing, which may include occurances within an attribute value
     */
    private function findAttributesPessimistic(string $attributes, callable $callback, array &$filtered): void
    {
        $filtered[] = preg_replace_callback(
            $this->pessimisticSearchRegex,
            function ($matches) use ($callback) {
                $attributeContents = '';
                if (isset($matches[2]) && $matches[2]) {
                    $attributeContents = $matches[2]; // quoted contents
                } elseif (isset($matches[3]) && $matches[3]) {
                    $attributeContents = $matches[3]; // unquoted contents
                }
                return $callback($matches[0], $attributeContents);
            },
            $attributes
        );
    }

    private function initPessimisticSearchRegex(array $attributes): string
    {
        $attributes = '(?:' . implode('|', $attributes) . ')';
        return implode('', [
            '#',
            '(?<!\w)',
            $attributes,
            '[^0-9a-z"\'=]*', // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
            '=',
            '(?:',
            '(["\'`])', // quoted
            '(.*?)',
            '\1', // quote character
            '|',
            '(?<!["\'`])', // unqouted
            '((?:[^ >])*)', // everything up to space or '>'
            ')',
            '#si',
        ]);
    }
}
