<?php

namespace Ion\Autoloading;

use \Ion\PackageInterface;
use \Ion\Autoloading\AutoLoaderSettingsInterface;

interface AutoloaderInterface {

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
