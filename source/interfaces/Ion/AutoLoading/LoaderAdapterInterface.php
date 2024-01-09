<?php

namespace ion\AutoLoading;

use \ion\PackageInterface;
use \ion\AutoLoading\AutoLoaderInterface;

interface LoaderAdapterInterface {

    static function create(AutoLoaderInterface $autoLoader, string $includePath): LoaderAdapterInterface;

    static function createCacheFilename(string $deploymentId): string;

    static function createDeploymentId(PackageInterface $package, string $includePath): string;

    function getDeploymentId(): string;

    function getAutoLoader(): AutoLoaderInterface;

    function getIncludePath(): string;

    function load(string $className): bool;

    function saveCache(): void;

    function loadCache(): bool;

}
