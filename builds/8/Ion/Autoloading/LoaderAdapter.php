<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace Ion\Autoloading;

/**
 * Description of Loader
 *
 * @author Justus
 */

use \Ion\PackageInterface;
use \Ion\Package;

abstract class LoaderAdapter implements LoaderAdapterInterface {        
    
    protected const CACHE_FILENAME_PREFIX = 'ion-auto-load';    
    protected const CACHE_FILENAME_EXTENSION = 'php';    
    protected const CACHE_HEADER_COMMENT = 'This file was auto-generated {$php_version}{$pkg_version}on {$time} and can be safely deleted.';
    protected const CACHE_HEADER = "<?php \n\n// " . self::CACHE_HEADER_COMMENT . "\n\n" . 'if(!defined(\'{$pkg_constant}\')) { header(\'HTTP/1.0 403 Forbidden\'); exit; }' . "\n\n";
    protected const CACHE_FUNCTION_NAME_PREFIX = '__ion_auto_load';
    protected const CACHE_CONSTANT_PREFIX = '__ION_CACHE_';  
    protected const CACHE_ENTRY_PATH_KEY = "path";

    public static function create(AutoloaderInterface $autoLoader, string $includePath): LoaderAdapterInterface {
        
        return new static($autoLoader, $includePath);
    }	
    
    public static function createCacheFilename(string $deploymentId): string {
        
        return static::CACHE_FILENAME_PREFIX . '-' . $deploymentId . '.' . static::CACHE_FILENAME_EXTENSION;
    }
    
    public static function createDeploymentId(PackageInterface $package, string $includePath): string {
        
        return md5($includePath . PHP_MAJOR_VERSION . PHP_MINOR_VERSION . ($package->getVersion() !== null ? $package->getVersion()->toString() : ''));
    }
    
    private static function strReplace(array $values, string $string): string {
        
        foreach($values as $key => $value) {
            
            $string = str_replace('{$' . $key . '}', $value, $string);
        }
        
        return $string;
    }    

    private $autoLoader = null;
    private $includePath = null;
    private $cache = [];
    private $newEntries = false;
    private $deploymentId = '';
    
    protected function __construct(AutoloaderInterface $autoLoader, string $includePath) {
        
        $this->autoLoader = $autoLoader;
        $this->includePath = $includePath;
        $this->cache = [];
        $this->newEntries = false;        
        $this->deploymentId = static::createDeploymentId($autoLoader->getPackage(), $includePath);
		
        //echo "AUTOLOADER: " . $this->deploymentId . "\n";
        
        if($autoLoader->getSettings()->isCacheEnabled())
            $this->loadCache();        
        
        if($autoLoader->getSettings()->isCacheEnabled() || ($autoLoader->getSettings()->isCacheEnabled() && defined(Autoloader::ENABLE_AUTOLOAD_DEBUG_DEFINITION) && constant(Autoloader::ENABLE_AUTOLOAD_DEBUG_DEFINITION) === true)) {                       
            
            $self = $this;
            
            register_shutdown_function(function() use ($self) {

                $self->saveCache();
            });
        }
    }    	

    private function getConstantName(): string {
        
        return static::CACHE_CONSTANT_PREFIX . $this->getDeploymentId();
    }
    
    public function getDeploymentId(): string {
        
            return $this->deploymentId;
    }
    
    public function getAutoloader(): AutoloaderInterface {
        
        return $this->autoLoader;
    }
    
    public function getIncludePath(): string {
        
        return $this->includePath;
    }
    
    protected abstract function loadClass(string $className): ?string;
    
    public final function load(string $className): bool {

        if($this->getAutoloader()->getSettings()->isCacheEnabled()) {
            
            if($this->hasCacheEntry($className)) {
                
                $path = $this->getCacheEntry($className)[self::CACHE_ENTRY_PATH_KEY];
                
                if(file_exists($path)) {
                
                    include($path);
                    return true;
                }
            }

            $path = $this->loadClass($className);
            
            if($path !== null) {

                $this->setCacheEntry($className, $path);                
                return true;
            }
            
            return false;
        }              
                
        return ($this->loadClass($className) !== null);
    }

    protected function hasCacheEntry(string $className): bool {
        
        if(!$this->getAutoloader()->getSettings()->isCacheEnabled())             
            return false;
        
        if(array_key_exists($className, $this->cache))
            return true;
        
        return false;
    }
    
    protected function getCacheEntry(string $className): ?array {
        
        if($this->hasCacheEntry($className))
            return $this->cache[$className];
        
        return null;
    }
    
    protected function setCacheEntry(string $className, string $path): void {

        if(!$this->hasCacheEntry($className))
            $this->newEntries = true;
        
        $this->cache[$className] = [ self::CACHE_ENTRY_PATH_KEY => $path ];
    }
    
    public function saveCache(): void {
                        
        if($this->newEntries || ($this->getAutoloader()->getSettings()->isCacheEnabled() && defined(Autoloader::ENABLE_AUTOLOAD_CACHE_DEFINITON) && constant(Autoloader::ENABLE_AUTOLOAD_CACHE_DEFINITON))) {
            
            $funcName = static::CACHE_FUNCTION_NAME_PREFIX . '_' . $this->getDeploymentId();

            $data = self::strReplace([
                
                'php_version' => 'for PHP version ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . ' ',
                'pkg_version' => ($this->getAutoloader()->getPackage()->getVersion() !== null ? 'and package version ' . $this->getAutoloader()->getPackage()->getVersion()->toString(). ', ' : ''),
                'time' => strftime('%c'),
                'pkg_constant' => $this->getConstantName()
                    
            ], static::CACHE_HEADER) . 'function &' . $funcName . '()' . (PHP_MAJOR_VERSION >= 7 ? ': array' : '') . " {\n\$array = " . var_export($this->cache, true) . ";\nreturn \$array;\n}";
            
            if(is_dir($this->getIncludePath()))
                file_put_contents($this->getIncludePath() . static::createCacheFilename($this->getDeploymentId()), $data);
            
            $this->newEntries = false;
        }
    }
    
    public function loadCache(): bool {
        
        $path = $this->getIncludePath() . static::createCacheFilename($this->getDeploymentId());
        $this->newEntries = false;
        
        if(file_exists($path)) {      
            
            if(!defined($this->getConstantName()))
                define($this->getConstantName(), true);
            
            include_once($path);
            
            $funcName = static::CACHE_FUNCTION_NAME_PREFIX . '_' . $this->getDeploymentId();
            
            if(!function_exists($funcName))
                return false;
            
            $this->cache = $funcName();

            return true;
        }
        
        return false;
    }
}
