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

    public const ENABLE_AUTOLOAD_CACHE_DEFINITON = 'ENABLE_AUTOLOAD_CACHE';
    public const ENABLE_AUTOLOAD_DEBUG_DEFINITION = 'ENABLE_AUTOLOAD_DEBUG';
        

    private static $instances = [];

    /**
     * 
     * Create a package instance.
     * 
     * @param array $developmentPaths The paths to the PHP source.
     * @param array $additionalPaths An optional list of additional relative directories to use as the root for auto-load functionality (prioritized above __$sourcePath__ if __$enableDebug__ is __FALSE__).     
     * @param bool $enableDebug Enable or disable debug mode. If __TRUE__ __$sourcePath__ will used as the auto-load root; if __FALSE__ __$includePaths__ will be searched before __$sourcePath__.
     * @param bool $enableCache Enable or disable the autoload cache - if NULL, checks if 'ENABLE_AUTOLOAD_CACHE' is __TRUE__ - if not, then it defaults to __FALSE__.
     * @param array $loaderClassNames A list of class names to instantiate as loaders - if __NULL__ the default is ['\ion\Packages\Adapters\Psr0Loader', '\ion\Packages\Adapters\Psr4Loader'].     
     * 
     * @return PackageInterface Returns the new package instance.
     * 
     */    
    
    public static function create(
            
            array $developmentPaths,
            array $additionalPaths = null,
            bool $enableDebug = null,
            bool $enableCache = null,
            array $loaderClassNames = null
            
        ): PackageInterface {
        
        return new static(
                
            $developmentPaths, 
            $additionalPaths, 
            $enableDebug, 
            $enableCache, 
            $loaderClassNames
        );
    }
        
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
    
    public static function createSearchPath(PackageInterface $package, string $path): ?string {
        
        $includePath = trim($package->getProjectRootDirectory(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        //echo $includePath . "\n";

        if(DIRECTORY_SEPARATOR === '/') {
            
            $includePath = DIRECTORY_SEPARATOR . $includePath;
        }

        $includePath = realpath($includePath);

        return ($includePath === false ? null : $includePath . DIRECTORY_SEPARATOR);        
    }

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

    protected function __construct(
        
            array $sourcePaths, 
            array $additionalPaths = null, 
            bool $enableDebug = null, 
            bool $enableCache = null, 
            array $loaderClassNames = null
    ) {

        $this->sourcePaths = $sourcePaths;
                
        $this->config = $this->loadConfiguration();
        
        // Enable source/debug mode?
        
        $this->enableDebug = null;
        
        if($enableDebug === null) {
                        
            if($this->isDependency() === false) {
                
                if(defined(static::ENABLE_AUTOLOAD_DEBUG_DEFINITION)) {
            
                    $this->enableDebug = (bool) constant(static::ENABLE_AUTOLOAD_DEBUG_DEFINITION) === true;  
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
            
            if(defined(static::ENABLE_AUTOLOAD_CACHE_DEFINITON)) {
            
                $this->enableCache = (bool) constant(static::ENABLE_AUTOLOAD_CACHE_DEFINITON) === true;                
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
        
        if(strstr($this->getPackage()->getProjectRootDirectory(), DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
            
            return true;
        }
        
        return null;
    }        
    
    protected function hasRepository(): bool {
        
        $repos = [ '.git', '.hg' ];
        
        foreach($repos as $repo) {
            
            if(is_dir($this->getPackage()->getProjectRootDirectory() . $repo)) {
                
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
