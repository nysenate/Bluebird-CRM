<?php

namespace Civi\CompilePlugin;

use Composer\Package\PackageInterface;

class PackageSorter
{

    /**
     * Given a list of installed packages sort such that dependant
     * packages come after there dependencies.
     *
     * TODO handle cycles.
     *
     * @param PackageInterface[] $installedPackages
     *   All installed packages, including the root.
     * @return array
     *   List of installed packages (sorted topologically).
     *
     *   Upstream packages with no dependencies come earlier than downstream packages that require them.
     *
     *   Ex: [0 => 'very-much/upstream', 1 => 'some-what/midstream', 2 => 'here-now/downstream']
     */
    public static function sortPackages($installedPackages)
    {
        // We do our own topological sort.  It doesn't seem particularly easy to ask composer for this list -- the
        // canonical sort for 'composer require' and 'composer update' have to address a lot of issues (like
        // version-selection and already-installed packages) that don't make sense here.

        // The topological sort may have multiple, equally-correct outputs. Given the same
        // packages as input (regardless of input-order), we want to produce stable output.
        usort($installedPackages, function ($a, $b) {
            return strnatcmp($a->getName(), $b->getName());
        });

        // Index mapping all known aliases (provides/replaces) to real names.
        // Array(string $logicalName => string $realName)
        $realNames = [];
        foreach ($installedPackages as $package) {
            /** @var PackageInterface $package */
            foreach ($package->getNames() as $alias) {
                $realNames[$alias] = $package->getName();
            }
        }

        // Array(string $realName => string[] $realNames)
        $realRequires = [];
        $addRealRequires = function ($package, $target) use (&$realRequires, &$realNames) {
            if (isset($realNames[$target]) && $realNames[$target] !== $package) {
                $realRequires[$package][] = $realNames[$target];
            }
        };
        foreach ($installedPackages as $package) {
            /** @var PackageInterface $package */
            foreach ($package->getRequires() as $link) {
                $addRealRequires($package->getName(), $link->getTarget());
            }
            // Unfortunately, cycles are common among suggests/dev-requires... ex: phpunit
            //foreach ($package->getDevRequires() as $link) {
            //    $addRealRequires($package->getName(), $link->getTarget());
            //}
            //foreach ($package->getSuggests() as $target => $comment) {
            //    $addRealRequires($package->getName(), $target);
            //}
        }

        // Unsorted list of packages that need to be visited.
        // Array(string $packageName => PackageInterface $package).
        $todoPackages = [];
        foreach ($installedPackages as $package) {
            $todoPackages[$package->getName()] = $package;
        }

        // The topologically sorted packages, from least-dependent to most-dependent.
        // Array(string $packageName => PackageInterface $package)
        $sortedPackages = [];

        // A package is "ripe" when all its requirements are met.
        $isRipe = function (PackageInterface $pkg) use (&$sortedPackages, &$realRequires) {
            foreach ($realRequires[$pkg->getName()] ?? [] as $target) {
                if (!isset($sortedPackages[$target])) {
                     // printf("[%s] is not ripe due to [%s]\n", $pkg->getName(), $target);
                    return false;
                }
            }
            // printf("[%s] is ripe\n", $pkg->getName());
            return true;
        };

        // A package is "consumed" when we move it from $todoPackages to $sortedPackages.
        $consumePackage = function (PackageInterface $pkg) use (&$sortedPackages, &$todoPackages) {
            $sortedPackages[$pkg->getName()] = $pkg;
            unset($todoPackages[$pkg->getName()]);
        };

        // Main loop: Progressively move ripe packages from $todoPackages to $sortedPackages.
        while (!empty($todoPackages)) {
            $ripePackages = array_filter($todoPackages, $isRipe);
            if (empty($ripePackages)) {
                $todoStr = implode(' ', array_map(
                    function ($p) {
                        return $p->getName();
                    },
                    $todoPackages
                ));
                throw new \RuntimeException("Error: Failed to find next installable package. Remaining: $todoStr");
            }
            foreach ($ripePackages as $package) {
                $consumePackage($package);
            }
        }

        return array_keys($sortedPackages);
    }
}
