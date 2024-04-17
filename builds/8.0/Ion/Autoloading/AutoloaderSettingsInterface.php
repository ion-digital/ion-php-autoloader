<?php
namespace Ion\Autoloading;

use Ion\PackageInterface;
interface AutoloaderSettingsInterface
{
    function getPackage() : PackageInterface;
    /**
     *
     * Returns whether the cache is enabled.
     *
     * @return bool Returns __true_ if the cache is enabled, __false__ if otherwise.
     *
     */
    function isCacheEnabled() : bool;
    /**
     *
     * Returns whether debug mode is enabled.
     *
     * @return bool Returns __true_ if the debug mode is enabled, __false__ if otherwise.
     *
     */
    function isDebugEnabled() : bool;
}