<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace Ion\Autoloading;

use \Ion\PackageInterface;
use \Ion\Disposable;

class AutoloaderSettings extends Disposable implements AutoloaderSettingsInterface {

    private const AUTOLOADER_JSON_FILENAME = "autoloader.json";
    private const ENABLE_DEBUG_VALUE_KEY = "debug";
    private const ENABLE_CACHE_VALUE_KEY = "cache";

    private static $instances = [];

    private static function getPath(PackageInterface $package, string $filename = null): string {

        $filename = $filename ?? self::AUTOLOADER_JSON_FILENAME;

        return "{$package->getProjectRootDirectory()}{$filename}";
    }

    public static function get(PackageInterface $package, AutoloaderSettingsInterface $defaults = null): AutoloaderSettingsInterface  {

        if(static::hasInstance($package))
            return static::getInstance($package);

        if($defaults !== null)
            return $defaults;

        return new static($package);
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

            $package,
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

    protected static function getInstance(PackageInterface $package): ?AutoloaderSettingsInterface {
        
        if(!static::hasInstance($package))
            return null;
    
        return static::$instances[$package->getName()];
    }    

    protected static function hasInstance(PackageInterface $package): bool {

        return (bool) array_key_exists($package->getName(), static::$instances);
    }    

    protected static function destroyInstance(AutoloaderSettingsInterface $settings): void {
        
        if(!static::hasInstance($settings->getPackage()))
            return;     

        unset(static::$instances[$settings->getPackage()->getName()]);
    }
    
    protected static function registerInstance(AutoloaderSettingsInterface $settings): void {

        if (static::hasInstance($settings->getPackage()))
            throw new AutoloaderSettingsException("Settings have already been registered for package: '{$settings->getPackage()->getName()}.'");

        static::$instances[$settings->getPackage()->getName()] = $settings;        
        return;
    }    

    private $package = null;
    private $values = [];

    public function __construct(
        
            PackageInterface $package, 
            bool $enableCache = true, 
            bool $enableDebug = false

        ) {

        $this->package = $package;

        $this->setValue(static::ENABLE_CACHE_VALUE_KEY, $enableCache);
        $this->setValue(static::ENABLE_DEBUG_VALUE_KEY, $enableDebug);

        static::registerInstance($this);
    }

    protected function dispose(bool $disposing) {

        static::destroyInstance($this);
    }

    protected function getValue(string $key, $default = null) {

        if(!array_key_exists($key, $this->values))
            return $default;

        return $this->values[$key];
    }

    protected function setValue(string $key, $value = null): void {

        $this->values[$key] = $value;
    }

    public function getPackage(): PackageInterface {

        return $this->package;
    }

    /**
     * 
     * Returns whether the cache is enabled.
     * 
     * @return bool Returns __true_ if the cache is enabled, __false__ if otherwise.
     * 
     */
    
     public function isCacheEnabled(): bool {
        
        return $this->getValue(static::ENABLE_CACHE_VALUE_KEY, false);
    }
    
    
    /**
     * 
     * Returns whether debug mode is enabled.
     * 
     * @return bool Returns __true_ if the debug mode is enabled, __false__ if otherwise.
     * 
     */    
    
    public function isDebugEnabled(): bool {
        
        return $this->getValue(static::ENABLE_DEBUG_VALUE_KEY, false);
    }        
}