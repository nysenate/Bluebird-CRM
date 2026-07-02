<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer;

/**
 * Trait FilterRunnerTrait
 * @package Phlib\XssSanitizer
 */
trait FilterRunnerTrait
{
    /**
     * Run the filters repeatedly until they no longer change the string
     *
     * @param FilterInterface[] $filters
     */
    private function runFilters(string $str, array $filters): string
    {
        do {
            $pre = $str;
            $str = $this->applyEachFilter($str, $filters);
        } while ($pre !== $str);

        return $str;
    }

    /**
     * Apply each filter in the filters array
     *
     * @param FilterInterface[] $filters
     */
    private function applyEachFilter(string $str, array $filters): string
    {
        foreach ($filters as $filter) {
            $str = $filter->filter($str);
        }
        return $str;
    }
}
