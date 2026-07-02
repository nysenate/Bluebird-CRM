<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\FilterRunnerTrait;

/**
 * @package Phlib\XssSanitizer
 */
class AttributeContentCleaner implements FilterInterface
{
    use FilterRunnerTrait;

    /**
     * @var FilterInterface[]
     */
    private array $filters;

    public function __construct()
    {
        $this->initFilters();
    }

    /**
     * Filters the content of an attribute
     * This should be decoding UTF-8 and HTML entities, and compacting any exploded words which we're searching for
     *
     * e.g.
     *     \u006A a v a &#115; c r i p t:alert('XSS');
     * should become
     *     javascript:alert('XSS');
     */
    public function filter(string $str): string
    {
        return $this->runFilters($str, $this->filters);
    }

    private function initFilters(): void
    {
        $this->filters = [
            new AttributeContent\DecodeUtf8(),
            new AttributeContent\DecodeEntities(),
            new AttributeContent\CompactExplodedWords(),
        ];
    }
}
