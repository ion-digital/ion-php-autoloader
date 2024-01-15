<?php

namespace Ion\Autoloading;

use \Ion\PackageInterface;

interface AutoloaderSettingsInterface {

    static function get(PackageInterface $package, AutoloaderSettingsInterface $defaults = null): AutoloaderSettingsInterface;

    static function load(PackageInterface $package, string $filename = null): AutoloaderSettingsInterface;

    static function exists(PackageInterface $package, string $filename = null): bool;

    function getPackage(): PackageInterface;

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

}
