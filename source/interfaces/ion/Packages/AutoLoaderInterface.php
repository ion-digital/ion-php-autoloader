<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion\Packages;

use ion\PackageInterface;
use ion\Package;



interface AutoLoaderInterface extends IAutoLoader {


    static function create(PackageInterface $package, string $includePath): AutoLoaderInterface;

    static function createCacheFilename(string $deploymentId): string;

    static function createDeploymentId(PackageInterface $package, string $includePath): string;

    function getDeploymentId(): string;

    function getPackage(): PackageInterface;

    function getIncludePath(): string;

    function load(string $className): bool;

    function saveCache(): void;

    function loadCache(): bool;
}
