<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer;

/**
 * Interface FilterInterface
 * @package Phlib\XssSanitizer
 */
interface FilterInterface
{
    public function filter(string $str): string;
}
