<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\FilterRunnerTrait;

/**
 * @package Phlib\XssSanitizer
 */
class FilterRunner implements FilterInterface
{
    use FilterRunnerTrait;

    /**
     * @var FilterInterface[]
     */
    private array $filters;

    /**
     * @param FilterInterface|FilterInterface[] $filters
     */
    public function __construct($filters)
    {
        if (!is_array($filters)) {
            $filters = [$filters];
        }
        $this->filters = $filters;
    }

    /**
     * Runs each of the filters against the string repeatedly
     */
    public function filter(string $str): string
    {
        return $this->runFilters($str, $this->filters);
    }
}
