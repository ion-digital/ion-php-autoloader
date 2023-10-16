<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion;

/**
 * Description of Package
 *
 * @author Justus
 */

use \ion\SemVerInterface;
use \ion\SemVer;
use \ion\Packages\PackageException;
use \ion\Packages\Adapters\Psr0Loader;
use \ion\Packages\Adapters\Psr4Loader;
use \ion\ConfigurationInterface;
use \ion\Configuration;

final class Package implements PackageInterface {

    private const PHP_VERSION_SEPARATOR = '.';
    
    public const COMPOSER_FILENAME = 'composer.json';
    public const ION_PACKAGE_CONFIGURATION_FILENAME = "autoloader.json";
    public const ION_PACKAGE_VERSION_FILENAME = 'version.json';
    public const ION_AUTOLOAD_CACHE = 'ION_AUTOLOAD_CACHE';
    public const ION_AUTOLOAD_CACHE_DEBUG = 'ION_AUTOLOAD_CACHE_DEBUG';    
    public const ION_PACKAGE_IGNORE_VERSION = 'ION_PACKAGE_IGNORE_VERSION';
    public const ION_PACKAGE_DEBUG = 'ION_PACKAGE_DEBUG';
    public const ION_PACKAGE_IGNORE_CONFIGURATION = 'ION_PACKAGE_IGNORE_CONFIGURATION';
    

    private static $instances = [];

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
    
    public static function create(
            
            string $vendor,
            string $project,
            array $developmentPaths,
            array $additionalPaths = null,
            string $projectRoot = null,
            SemVerInterface $version = null,
            bool $enableDebug = null,
            bool $enableCache = null,
            array $loaderClassNames = null
            
        ): PackageInterface {
        
        return new static(
                
            $vendor, 
            $project, 
            $developmentPaths, 
            $additionalPaths, 
            $projectRoot, 
            $version, 
            $enableDebug, 
            $enableCache, 
            $loaderClassNames
        );
    }
        
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
    
    public static function createSearchPath(PackageInterface $package, string $path): ?string {
        
        $includePath = trim($package->getProjectRoot(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        //echo $includePath . "\n";

        if(DIRECTORY_SEPARATOR === '/') {
            
            $includePath = DIRECTORY_SEPARATOR . $includePath;
        }

        $includePath = realpath($includePath);

        return ($includePath === false ? null : $includePath . DIRECTORY_SEPARATOR);        
    }
    
    /**
     * 
     * Return all registered package instances.
     * 
     * @return array An array containing all registered package instances.
     * 
     */    

    public static function getInstances(): array {
        
        return static::$instances;
    }
    
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

    public static function hasInstance(string $vendorName, string $projectName): bool {

        return (bool) array_key_exists($vendorName . '/' . $projectName, static::$instances);
    }
    
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
    
    public static function getInstance(string $vendorName, string $projectName): ?PackageInterface {
        
        if(!static::hasInstance($vendorName, $projectName)) {

            return null;
        }
    
        return static::$instances[$vendorName . '/' . $projectName];
    }

    protected static function destroyInstance(self $instance): void {
        
        unset(static::$instances[$instance->getName()]);
    }
    
    protected static function registerInstance(self $instance): void {

        if ($instance->getVersion() !== null) {
            
            if (array_key_exists($instance->getName(), static::$instances) === true) {

                $tmp = static::$instances[$instance->getName()];
                
                if ($tmp->getVersion() !== null) {
                    
                    if ($instance->getVersion()->isLowerThan($tmp->getVersion())) {
                        
                        static::$instances[$instance->getName()]->destroy();
                    }
                }
            }
        }

        static::$instances[$instance->getName()] = $instance;
        
        return;
    }

    /**
     * 
     * Get the directory of the last function/method call (or further, depending on $back).
     * 
     * @param int $back The number of times / steps to trace back.
     * 
     * @return string Return the resulting directory.
     * 
     */    
    
    public static function getCallingDirectory(int $back = 1): string {

        $trace = debug_backtrace();

        if ($back > count($trace)) {
            
            $back = count($trace) - 1;
        }

        for ($i = 0; $i < $back; $i++) {
            
            array_shift($trace);
        }

        $trace = array_values($trace);

        return dirname($trace[array_search(__FUNCTION__, array_column($trace, 'function'))]['file']) . DIRECTORY_SEPARATOR;
    }

    private $vendor = null;
    private $project = null;
    private $version = null;
    private $name = null;
    private $projectRoot = null;
    private $projectEntry = null;
    private $includePaths = []; 
    private $sourcePaths = null;
    private $searchPaths = [];    
    private $hooks = [];
    private $loaders = [];
    private $enableCache = false;
    private $enableDebug = false;
    private $cache = [];
    private $config = null;
    private $hooksRegistered = false;

    protected function __construct(string $vendor, string $project, array $sourcePaths, array $additionalPaths = null, string $projectRoot = null, SemVerInterface $version = null, bool $enableDebug = null, bool $enableCache = null, array $loaderClassNames = null) {

        $this->vendor = $vendor;
        $this->project = $project;
        $this->name = $vendor . '/' . $project;
        $this->sourcePaths = $sourcePaths;
        
        $tmp = null;
        
        if ($projectRoot === null) {
            
            $tmp = static::getCallingDirectory();
            
        } else {
            
            $tmp = $projectRoot;
        }

        $tmp = realpath($tmp);
        
        if(empty($tmp)) {
            
            throw new PackageException("Project root / entry '{$projectRoot}' for package '{$vendor}/{$project}' is invalid.");
        }
        
        if(!is_dir($tmp)) {
            
            $this->projectEntry = $tmp;                                    
            $this->projectRoot = pathinfo($tmp . DIRECTORY_SEPARATOR, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
            
        } else {
            
            $this->projectRoot = $tmp . DIRECTORY_SEPARATOR;
        }
        
        $this->config = $this->loadConfiguration();
        
        // Enable source/debug mode?
        
        $this->enableDebug = null;
        
        if($enableDebug === null) {
                        
            if($this->isDependency() === false) {
                
                if(defined(static::ION_PACKAGE_DEBUG)) {
            
                    $this->enableDebug = (bool) constant(static::ION_PACKAGE_DEBUG) === true;  
                }
            }
            
            if($this->enableDebug === null && $this->hasDebugIndicator()) {
                    
                $this->enableDebug = true;
            }
            
            if($this->enableDebug === null && $this->hasRepository() && ($this->isDependency() === false)) {
                    
                $this->enableDebug = true;
            }             
            
            if($this->enableDebug === null) {
                
                $this->enableDebug = false;
            }
            
        } else {
            
            $this->enableDebug = $enableDebug;
        }                 

        // Use the cache?

        $this->enableCache = null;
        
        if($enableCache === null) {
            
            if(defined(static::ION_AUTOLOAD_CACHE)) {
            
                $this->enableCache = (bool) constant(static::ION_AUTOLOAD_CACHE) === true;                
            }
            
            if($this->enableCache === null && $this->hasCacheIndicator()) {
                    
                $this->enableCache = true;
            }            
            
            if($this->enableCache === null && $this->enableDebug === true) {
                
                $this->enableCache = false;
            }
                        
            if($this->enableCache === null) {
                
                $this->enableCache = true;
            }            
            
        } else {
            
            $this->enableCache = $enableCache;
        }

        
        $this->version = $version;
        
        if($this->version === null) {
            
            $this->version = $this->loadVersion();
        }          
        
        $this->includePaths = $additionalPaths;

        if($this->includePaths === null) {
            
            $this->includePaths = [];
        }       
        
        $tmpPaths = $this->includePaths;        
        
        if($this->enableDebug) { 
            
            $tmpPaths = []; // Override if 'debug' is true
        }
        
        // Add the dev directories at the end
        $tmpPaths = array_merge($tmpPaths, $sourcePaths);

        $this->searchPaths = [];
                
        foreach ($tmpPaths as $path) {
            
            $includePath = static::createSearchPath($this, $path);
            
            if($includePath !== null) {            
                
                $this->searchPaths[] = $includePath;

                if($loaderClassNames === null || (is_array($loaderClassNames) && count($loaderClassNames) === 0)) {

                    $psr0 = Psr0Loader::class;
                    $psr4 = Psr4Loader::class;

                    $this->loaders[] = $psr0::create($this, $includePath);
                    $this->loaders[] = $psr4::create($this, $includePath);
                } else {      

                    foreach($loaderClassNames as $loaderClassName) {

                        if(!class_exists($loaderClassName)) {
                            throw new PackageException("'$loaderClassName' does not exist and cannot be used as an auto-loader.");
                        }

                        $this->loaders[] = $loaderClassName::create($this, $includePath);
                    }            
                }                                    
            }
        }
        
        static::registerInstance($this);
        
        $this->registerLoaders();

    }
    
    protected function isDependency(): ?bool { // NULL = Possibly, not sure; TRUE = Definitely yes; FALSE = Definitely no.
        
        //.gitignore? .git? composer.json? /vendor ? version.json? .hg? .hgignore?
        
        if(strstr($this->projectRoot, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
            
            return true;
        }
        
        return null;
    }        
    
    protected function hasRepository(): bool {
        
        $repos = [ '.git', '.hg' ];
        
        foreach($repos as $repo) {
            
            if(is_dir($this->projectRoot . DIRECTORY_SEPARATOR . $repo)) {
                
                return true;
            }
        }
        
        return false;
    }
    
    protected function hasDebugIndicator(): bool {
    
        return $this->getConfiguration()->getSettingAsBool('debug');        
    }
    
    protected function hasCacheIndicator(): bool {
    
        return $this->getConfiguration()->getSettingAsBool('cache');        
    }    
    
    protected function registerLoaders(): void {
        if (count($this->getHooks()) === 0) {

            try {

                $self = $this;
                
                foreach ($this->loaders as $index => $loader) {
                    $this->hooks[] = function(string $className) use ($index, $loader, $self) {
                        $loader->load($className);
                    };
                }
                
                foreach ($this->hooks as $hook) {
                    spl_autoload_register($hook, true, false);
                }
                
            } catch (Exception $ex) {
                throw $ex;
            }
        }

        return;
    }
    
    /**
     * 
     * Destroy an instance.
     *
     * @return void
     * 
     */    

    public function destroy(): void {
        if (count($this->getHooks()) > 0) {

            foreach ($this->hooks as $hook) {
                spl_autoload_unregister($hook);
            }

            $this->hooksRegistered = false;
        }
        
        static::destroyInstance($this);
        
        return;
    }
    
    /**
     * 
     * Get the registered auto-load hooks.
     * 
     * @return array Returns all registered auto-load hooks.
     * 
     */    
    
    public function getHooks(): array {
        
        return $this->hooks;
    }
    
    /**
     * 
     * Get the registered loaders.
     * 
     * @return array Returns all registered loaders.
     * 
     */    
    
    public function getLoaders(): array {
        
        return $this->loaders;
    }
    
    /**
     * 
     * Get the the package configuration.
     * 
     * @return ConfigurationInterface Returns all registered loaders.
     * 
     */        
    
    public function getConfiguration(): ConfigurationInterface {
        
        if($this->config === null) {
            
            $this->config = $this->loadConfiguration();
        }
        
        return $this->config;
    }

    protected function getVendorRoot(string $includePath, int $phpMajorVersion = null, int $phpMinorVersion = null): string {

        if ($phpMajorVersion !== null || ($phpMajorVersion !== null && $phpMinorVersion !== null)) {

            if ($phpMinorVersion !== null) {
                return $includePath . DIRECTORY_SEPARATOR . $phpMajorVersion . static::PHP_VERSION_SEPARATOR . $phpMinorVersion . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
            }

            return $includePath . DIRECTORY_SEPARATOR . $phpMajorVersion . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
        }

        return $includePath . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
    }
      
    protected function loadConfiguration(): ConfigurationInterface {

        if(defined(static::ION_PACKAGE_IGNORE_CONFIGURATION) && (constant(static::ION_PACKAGE_IGNORE_CONFIGURATION) === true)) {
            
            return new Configuration([]);
        }
        
        $data = null;        
        
        $path = $this->getProjectRoot() . DIRECTORY_SEPARATOR . static::ION_PACKAGE_CONFIGURATION_FILENAME;
        
        if(file_exists($path)) {

            $data = file_get_contents($path);            
        }        
        
        if(empty($data)) {
            
            return new Configuration([]);
        }
        
        return Configuration::parseJson($data);
    }

    protected function loadVersion(): ?SemVerInterface {
        
        if(defined(static::ION_PACKAGE_IGNORE_VERSION) && (constant(static::ION_PACKAGE_IGNORE_VERSION) === true)) {
            
            return null;
        }

        $path = $this->getProjectRoot() . static::ION_PACKAGE_VERSION_FILENAME;
        
        if(file_exists($path)) {
        
            $data = file_get_contents($path);
            
            if($data !== false) {
            
                $version = SemVer::parsePackageJson($data);

                if($version !== null) {
                    return $version;
                }
            }
        }
        
        $path = $this->getProjectRoot() . static::COMPOSER_FILENAME;

        if(file_exists($path)) {   

            $data = file_get_contents($path);

            if($data !== false) {
                
                return SemVer::parseComposerJson($data);
            }
        }        
        
        return null;
    }

    /**
     * 
     * Get the package version.
     * 
     * @return ?SemVerInterface Returns the specified version of the package, or null if not specified.
     * 
     */    

    public function getVersion(): ?SemVerInterface {
        
        return $this->version;
    }

    /**
     * 
     * Get the package vendor name.
     * 
     * @return string Returns the vendor name.
     * 
     */    
    
    public function getVendor(): string {
        
        return $this->vendor;
    }

    /**
     * 
     * Get the package project name.
     *
     * @return string Returns the project name.
     * 
     */   
    
    public function getProject(): string {
        
        return $this->project;
    }
    
    /**
     * Get the package name (in the format vendor/project).
     *
     * @return string Returns the package name (in the format vendor/project).
     * 
     */  

    public function getName(): string {
        
        return $this->name;
    }

    /**
     * Get the project root directory.
     *
     * @return string Returns the project root directory.
     * 
     */    
    
    public function getProjectRoot(): string {
        
        return $this->projectRoot;
    }
    
    /**
     * 
     * Get the project entry file (if available)
     * 
     */
    
    public function getProjectEntry(): ?string {
        
        return $this->projectEntry;
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
    
    /**
     * 
     * Forces all cache items to be saved immediately, and don't wait for shut-down.
     * 
     */
    
    public function flushCache(): void {
        
        foreach($this->loaders as $loader) {
            
            $loader->saveCache();
        }
    }
    
    /**
     * 
     * Returns the cache array.
     * 
     * @return array Returns the cache array.
     * 
     */    
    
    public function getCache(): array {
        
        return $this->cache;
    }
    
    /**
     * 
     * Returns the development path array.
     * 
     * @return array Returns the source path array.
     * 
     */ 
    
    public function getDevelopmentPaths(): array {
        
        return $this->sourcePaths;
    }
    
    /**
     * 
     * Returns the additional path array.
     * 
     * @return array Returns the include path array.
     * 
     */    
    
    public function getAdditionalPaths(): array {
        
        return $this->includePaths;
    }
    
    /**
     * 
     * Returns the resulting include path array (development paths and additional paths - depending on the package debug setting).
     *
     * @return array Returns the final include path array.
     * 
     */   
    
    public function getSearchPaths(): array {
        
        return $this->searchPaths;   
    }

}
