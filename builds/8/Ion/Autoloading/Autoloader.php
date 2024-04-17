<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace Ion\Autoloading;

/**
 * Description of Package
 *
 * @author Justus
 */

use \Ion\PackageInterface;
use \Ion\Disposable;
use \Ion\SemVerInterface;
use \Ion\SemVer;
use \Ion\Autoloading\AutoloaderException;
use \Ion\Autoloading\Adapters\Psr0LoaderAdapter;
use \Ion\Autoloading\Adapters\Psr4LoaderAdapter;

final class Autoloader extends Disposable implements AutoloaderInterface {

    public const ENABLE_AUTOLOAD_CACHE_DEFINITON = 'ENABLE_AUTOLOAD_CACHE';
    public const ENABLE_AUTOLOAD_DEBUG_DEFINITION = 'ENABLE_AUTOLOAD_DEBUG';

    private const CACHE_SETTING_KEY = "cache";
    private const DEBUG_SETTING_KEY = "debug";

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
    
    public static function create(
            
        PackageInterface $package,
        array $developmentPaths,
        array $additionalPaths = null,
        AutoloaderSettings $settings = null,
        array $loaderClassNames = null
        
    ): AutoloaderInterface {
        
        return new static(
                
            $package,
            $developmentPaths, 
            $additionalPaths, 
            $settings, 
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

        if(DIRECTORY_SEPARATOR === '/')    
            $includePath = DIRECTORY_SEPARATOR . $includePath;        

        $includePath = realpath($includePath);

        return ($includePath === false ? null : $includePath . DIRECTORY_SEPARATOR);        
    }

    private $package = null;
    private $includePaths = []; 
    private $sourcePaths = null;
    private $searchPaths = [];    
    private $hooks = [];
    private $loaders = [];
    private $cache = [];
    private $hooksRegistered = false;    

    protected function __construct(
        
        PackageInterface $package,
        array $sourcePaths, 
        array $additionalPaths = null, 
        AutoloaderSettings $settings = null,
        array $loaderClassNames = null

    ) {

        $this->package = $package;
        $this->sourcePaths = $sourcePaths;       

        $packageSettings = $this->getPackage()->getSettings();

        if(AutoloaderSettings::exists($package))
            $settings = $settings ?? AutoloaderSettings::load($package);

        if($settings === null) {

            // Enable source/debug mode?
            
            $enableDebug = false;

            if($this->isDependency() === false) {
                
                if(defined(static::ENABLE_AUTOLOAD_DEBUG_DEFINITION))
                    $enableDebug = (bool) constant(static::ENABLE_AUTOLOAD_DEBUG_DEFINITION) === true;  
            }

            if($enableDebug == null && $settings !== null)
                $enableDebug = $settings->isDebugEnabled();

            if($enableDebug == null && $packageSettings->has(self::DEBUG_SETTING_KEY))
                $enableDebug = $packageSettings->getAsBool(self::DEBUG_SETTING_KEY);
            
            if($enableDebug === null && $this->hasRepository() && ($this->isDependency() === false))
                $enableDebug = true;
            
            if($enableDebug === null)
                $enableDebug = false;

            // Use the cache?

            $enableCache = true;

            if(defined(static::ENABLE_AUTOLOAD_CACHE_DEFINITON))
                $enableCache = (bool) constant(static::ENABLE_AUTOLOAD_CACHE_DEFINITON) === true;                
            
            if($enableCache == null && $settings !== null)
                $enableCache = $settings->isCacheEnabled();

            if($enableCache == null && $packageSettings->has(self::CACHE_SETTING_KEY))
                $enableCache = $packageSettings->getAsBool(self::CACHE_SETTING_KEY);
            
            if($enableCache === null && $enableDebug === true)
                $enableCache = false;
                        
            if($enableCache === null)
                $enableCache = true;

            $settings = new AutoLoaderSettings(
            
                $this->getPackage(),
                $enableCache, 
                $enableDebug
            );
        }  
       
        $this->includePaths = $additionalPaths;

        if($this->includePaths === null)
            $this->includePaths = [];
        
        $tmpPaths = $this->includePaths;        
        
        if($this->getSettings()->isDebugEnabled())
            $tmpPaths = []; // Override if 'debug' is true
        
        // Add the dev directories at the end
        $tmpPaths = array_merge($tmpPaths, $sourcePaths);

        $this->searchPaths = [];
                
        foreach ($tmpPaths as $path) {
            
            $includePath = static::createSearchPath($this->package, $path);
            
            if($includePath !== null) {            
                
                $this->searchPaths[] = $includePath;

                if($loaderClassNames === null || (is_array($loaderClassNames) && count($loaderClassNames) === 0)) {

                    $psr0 = Psr0LoaderAdapter::class;
                    $psr4 = Psr4LoaderAdapter::class;

                    $this->loaders[] = $psr0::create($this, $includePath);
                    $this->loaders[] = $psr4::create($this, $includePath);

                } else {      

                    foreach($loaderClassNames as $loaderClassName) {

                        if(!class_exists($loaderClassName))
                            throw new AutoloaderException("'$loaderClassName' does not exist and cannot be used as an auto-loader.");

                        $this->loaders[] = $loaderClassName::create($this, $includePath);
                    }            
                }                                    
            }
        }

        $this->registerLoaders();
    }
    
    protected function isDependency(): ?bool { // NULL = Possibly, not sure; TRUE = Definitely yes; FALSE = Definitely no.
        
        //.gitignore? .git? composer.json? /vendor ? version.json? .hg? .hgignore?
        
        if(strstr($this->getPackage()->getProjectRootDirectory(), DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR))
            return true;
        
        return null;
    }        
    
    protected function hasRepository(): bool {
        
        $repos = [ '.git', '.hg' ];
        
        foreach($repos as $repo) {
            
            if(is_dir($this->getPackage()->getProjectRootDirectory() . $repo))
                return true;
        }
        
        return false;
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

    public function getPackage(): PackageInterface {

        return $this->package;
    }

    public function getSettings(): AutoLoaderSettingsInterface {

        return AutoLoaderSettings::get($this->getPackage());
    }
    
    /**
     * 
     * Destroy an instance.
     *
     * @return void
     * 
     */    

     protected function dispose(bool $disposing) {
        
        if (count($this->getHooks()) > 0) {

            foreach ($this->hooks as $hook) {

                spl_autoload_unregister($hook);
            }

            $this->hooksRegistered = false;
        }
        
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
