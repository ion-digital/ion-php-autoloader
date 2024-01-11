<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace Ion\Autoloading;

use \Ion\PackageInterface;

class AutoloaderSettings implements AutoloaderSettingsInterface {

    private const AUTOLOADER_JSON_FILENAME = "autoloader.json";

    private $enableCache = false;
    private $enableDebug = false;

    private static function getPath(PackageInterface $package, string $filename = null): string {

        $filename = $filename ?? self::AUTOLOADER_JSON_FILENAME;

        return "{$package->getProjectRootDirectory()}{$filename}";
    }

    public static function load(PackageInterface $package, string $filename = null): AutoloaderSettingsInterface  {

        $path = self::getPath($package, $filename);

        if(!self::exists($package, $filename))
            throw new AutoloaderException("Autoloader JSON settings file does not exist ('{$path}').");

        $json = @file_get_contents($path);

        if($json === false)
            throw new AutoloaderException("Could not load JSON settings from '{$path}.'");

        $json = trim($json);

        if(strlen($json) === 0)
            throw new AutoloaderException("Empty auto-loader JSON settings data, loaded from '{$path}.'");

        $obj = json_decode($json, false);

        if(empty($obj))
            throw new AutoloaderException("Invalid auto-loader JSON settings data, loaded from '{$path}.'");

        return new AutoloaderSettings(

            isset($obj->cache) ? $obj->cache : false, 
            isset($obj->debug) ? $obj->debug : false
        );
    }

    public static function exists(PackageInterface $package, string $filename = null): bool {

        $path = self::getPath($package, $filename);

        if(!file_exists($path))
            return false;

        return true;
    }

    public function __construct(bool $enableCache = true, bool $enableDebug = false) {

        $this->enableCache = $enableCache;
        $this->enableDebug = $enableDebug;
    }

    /**
     * 
     * Returns whether the cache is enabled.
     * 
     * @return bool Returns __true_ if the cache is enabled, __false__ if otherwise.
     * 
     */
    
     public function isCacheEnabled(): bool {
        
        return $this->enableCache;
    }
    
    
    /**
     * 
     * Returns whether debug mode is enabled.
     * 
     * @return bool Returns __true_ if the debug mode is enabled, __false__ if otherwise.
     * 
     */    
    
    public function isDebugEnabled(): bool {
        
        return $this->enableDebug;
    }        
}