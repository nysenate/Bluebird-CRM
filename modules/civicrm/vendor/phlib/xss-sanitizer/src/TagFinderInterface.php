<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer;

/**
 * Interface TagFinderInterface
 * @package Phlib\XssSanitizer
 */
interface TagFinderInterface
{
    /**
     * Given a full html string, finds the required tags by either tag name or attribute and calls the callback,
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
    public function findTags(string $str, callable $callback): string;
}
