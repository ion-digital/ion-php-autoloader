<?php

namespace ion;

use \ion\SemVerInterface;
use \ion\ConfigurationInterface;

interface PackageInterface {

    /**
     *
     * Create a package instance.
     *
     * @param string $vendor The vendor name (__vendor__/project).
     * @param string $project The project name (vendor/__project__).
     * @param array $developmentPaths The paths to the PHP source.
     * @param array $additionalPaths An optional list of additional relative directories to use as the root for auto-load functionality (prioritized above __$sourcePath__ if __$enableDebug__ is __FALSE__).
     * @param string $projectRoot An optional parameter to override the project root, if needed.
     * @param SemVerInterface $version The current package version - will be loaded from file, if __NULL__ and if a version definition file exists, or a Composer version tag is available (in _composer.json_).
     * @param bool $enableDebug Enable or disable debug mode. If __TRUE__ __$sourcePath__ will used as the auto-load root; if __FALSE__ __$includePaths__ will be searched before __$sourcePath__.
     * @param bool $enableCache Enable or disable the autoload cache - if NULL, checks if '_ION_AUTOLOAD_CACHE_' is __TRUE__ - if not, then it defaults to __FALSE__.
     * @param array $loaderClassNames A list of class names to instantiate as loaders - if __NULL__ the default is ['\ion\Packages\Adapters\Psr0Loader', '\ion\Packages\Adapters\Psr4Loader'].
     *
     * @return IPackage Returns the new package instance.
     *
     */

    static function create(

        string $vendor,
        string $project,
        array $developmentPaths,
        array $additionalPaths = null,
        string $projectRoot = null,
        SemVerInterface $version = null,
        bool $enableDebug = null,
        bool $enableCache = null,
        array $loaderClassNames = null

    ): PackageInterface;

    /**
     *
     * Create a search path string for the package.
     *
     * @param IPackage $package The package.
     * @param string $path The include path.
     *
     * @return ?string __NULL__ if no string could be created, the string if it could.
     *
     */

    static function createSearchPath(PackageInterface $package, string $path): ?string;

    /**
     *
     * Return all registered package instances.
     *
     * @return array An array containing all registered package instances.
     *
     */

    static function getInstances(): array;

    /**
     *
     * Check if a package has been registered.
     *
     * @param string $vendorName The package vendor name.
     * @param string $projectName The package project name.
     *
     * @return bool Returns __true__ if the package as been registered, __false__ if not.
     *
     */

    static function hasInstance(string $vendorName, string $projectName): bool;

    /**
     *
     * Get a package instance by package name.
     *
     * @param string $vendorName The package vendor name.
     * @param string $projectName The package project name.
     *
     * @return IPackage Returns the registered package instance.
     *
     */

    static function getInstance(string $vendorName, string $projectName): ?PackageInterface;

    /**
     *
     * Get the directory of the last function/method call (or further, depending on $back).
     *
     * @param int $back The number of times / steps to trace back.
     *
     * @return string Return the resulting directory.
     *
     */

    static function getCallingDirectory(int $back = 1): string;

    /**
     *
     * Destroy an instance.
     *
     * @return void
     *
     */

    function destroy(): void;

    /**
     *
     * Get the registered auto-load hooks.
     *
     * @return array Returns all registered auto-load hooks.
     *
     */

    function getHooks(): array;

    /**
     *
     * Get the registered loaders.
     *
     * @return array Returns all registered loaders.
     *
     */

    function getLoaders(): array;

    /**
     *
     * Get the the package configuration.
     *
     * @return ConfigurationInterface Returns all registered loaders.
     *
     */

    function getConfiguration(): ConfigurationInterface;

    /**
     *
     * Get the package version.
     *
     * @return ?SemVerInterface Returns the specified version of the package, or null if not specified.
     *
     */

    function getVersion(): ?SemVerInterface;

    /**
     *
     * Get the package vendor name.
     *
     * @return string Returns the vendor name.
     *
     */

    function getVendor(): string;

    /**
     *
     * Get the package project name.
     *
     * @return string Returns the project name.
     *
     */

    function getProject(): string;

    /**
     * Get the package name (in the format vendor/project).
     *
     * @return string Returns the package name (in the format vendor/project).
     *
     */

    function getName(): string;

    /**
     * Get the project root directory.
     *
     * @return string Returns the project root directory.
     *
     */

    function getProjectRoot(): string;

    /**
     *
     * Get the project entry file (if available)
     *
     */

    function getProjectEntry(): ?string;

    /**
     *
     * Returns whether the cache is enabled.
     *
     * @return bool Returns __true_ if the cache is enabled, __false__ if otherwise.
     *
     */

    function isCacheEnabled(): bool;

    /**
     *
     * Returns whether debug mode is enabled.
     *
     * @return bool Returns __true_ if the debug mode is enabled, __false__ if otherwise.
     *
     */

    function isDebugEnabled(): bool;

    /**
     *
     * Forces all cache items to be saved immediately, and don't wait for shut-down.
     *
     */

    function flushCache(): void;

    /**
     *
     * Returns the cache array.
     *
     * @return array Returns the cache array.
     *
     */

    function getCache(): array;

    /**
     *
     * Returns the development path array.
     *
     * @return array Returns the source path array.
     *
     */

    function getDevelopmentPaths(): array;

    /**
     *
     * Returns the additional path array.
     *
     * @return array Returns the include path array.
     *
     */

    function getAdditionalPaths(): array;

    /**
     *
     * Returns the resulting include path array (development paths and additional paths - depending on the package debug setting).
     *
     * @return array Returns the final include path array.
     *
     */

    function getSearchPaths(): array;

}
