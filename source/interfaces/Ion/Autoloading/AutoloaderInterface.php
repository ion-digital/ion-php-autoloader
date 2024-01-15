<?php

namespace Ion\Autoloading;

use \Ion\PackageInterface;
use \Ion\Autoloading\AutoloaderSettings;
use \Ion\Autoloading\AutoLoaderSettingsInterface;

interface AutoloaderInterface {

    /**
     *
     * Create a package instance.
     *
     * @param PackageInterface $package
     * @param array $developmentPaths The paths to the PHP source.
     * @param array $additionalPaths An optional list of additional relative directories to use as the root for auto-load functionality (prioritized above __$sourcePath__ if __$enableDebug__ is __FALSE__).
     * @param bool $enableDebug Enable or disable debug mode. If __TRUE__ __$sourcePath__ will used as the auto-load root; if __FALSE__ __$includePaths__ will be searched before __$sourcePath__.
     * @param bool $enableCache Enable or disable the autoload cache - if NULL, checks if 'ENABLE_AUTOLOAD_CACHE' is __TRUE__ - if not, then it defaults to __FALSE__.
     * @param array $loaderClassNames A list of class names to instantiate as loaders - if __NULL__ the default is ['\ion\Packages\Adapters\Psr0Loader', '\ion\Packages\Adapters\Psr4Loader'].
     *
     * @return AutoloaderInterface Returns the new package instance.
     *
     */

    static function create(

        PackageInterface $package,
        array $developmentPaths,
        array $additionalPaths = null,
        AutoloaderSettings $settings = null,
        array $loaderClassNames = null

    ): AutoloaderInterface;

    /**
     *
     * Create a search path string for the package.
     *
     * @param PackageInterface $package The package.
     * @param string $path The include path.
     *
     * @return ?string __NULL__ if no string could be created, the string if it could.
     *
     */

    static function createSearchPath(PackageInterface $package, string $path): ?string;

    function getPackage(): PackageInterface;

    function getSettings(): AutoLoaderSettingsInterface;

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
