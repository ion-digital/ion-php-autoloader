<?php

namespace Ion\Autoloading;

use \Ion\PackageInterface;
use \Ion\Autoloading\AutoloaderInterface;

interface LoaderAdapterInterface {

    static function create(AutoloaderInterface $autoLoader, string $includePath): LoaderAdapterInterface;

    static function createCacheFilename(string $deploymentId): string;

    static function createDeploymentId(PackageInterface $package, string $includePath): string;

    function getDeploymentId(): string;

    function getAutoloader(): AutoloaderInterface;

    function getIncludePath(): string;

    function load(string $className): bool;

    function saveCache(): void;

    function loadCache(): bool;

}
