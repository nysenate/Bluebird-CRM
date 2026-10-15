<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter\AttributeContent;

use Phlib\XssSanitizer\FilterInterface;

/**
 * @package Phlib\XssSanitizer
 */
class CompactExplodedWords implements FilterInterface
{
    private string $wordsRegex;

    public function __construct()
    {
        $this->wordsRegex = $this->buildWordsRegex();
    }

    /**
     * Compacts certain potentially dangerous words which have had whtespace added between the letters
     *
     * e.g.
     *     j a v a s c r i p t
     * becomes
     *     javascript
     */
    public function filter(string $str): string
    {
        $str = preg_replace_callback(
            $this->wordsRegex,
            function ($matches): string {
                return preg_replace('/\s+/', '', $matches[1]) . $matches[2];
            },
            $str
        );

        return $str;
    }

    private function buildWordsRegex(): string
    {
        $rawWords = [
            'javascript',
            'refresh', /* @see Phlib\XssSanitizer\Filter\MetaRefresh */
        ];

        $words = [];
        foreach ($rawWords as $word) {
            $words[] = chunk_split($word, 1, '\s*');
        }

        return implode('', [
            '#(', implode('|', $words), ')(\W|$)#is',
        ]);
    }
}
