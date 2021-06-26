<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion\Packages;

/**
 * Description of Loader
 *
 * @author Justus
 */
use ion\IPackage;
use ion\Package;
abstract class AutoLoader implements IAutoLoader
{
    const CACHE_FILENAME_PREFIX = 'ion-auto-load';
    const CACHE_FILENAME_EXTENSION = 'php';
    const CACHE_HEADER_COMMENT = 'This file was auto-generated {$php_version}{$pkg_version}on {$time} and can be safely deleted.';
    const CACHE_HEADER = "<?php \n\n// " . self::CACHE_HEADER_COMMENT . "\n\n" . 'if(!defined(\'{$pkg_constant}\')) { header(\'HTTP/1.0 403 Forbidden\'); exit; }' . "\n\n";
    const CACHE_FUNCTION_NAME_PREFIX = '__ion_auto_load';
    const CACHE_CONSTANT_PREFIX = '__ION_CACHE_';
    /**
     * method
     * 
     * 
     * @return IAutoLoader
     */
    public static function create(IPackage $package, string $includePath) : IAutoLoader
    {
        return new static($package, $includePath);
    }
    /**
     * method
     * 
     * 
     * @return string
     */
    public static function createCacheFilename(string $deploymentId) : string
    {
        return static::CACHE_FILENAME_PREFIX . '-' . $deploymentId . '.' . static::CACHE_FILENAME_EXTENSION;
    }
    /**
     * method
     * 
     * 
     * @return string
     */
    public static function createDeploymentId(IPackage $package, string $includePath) : string
    {
        return md5($includePath . PHP_MAJOR_VERSION . PHP_MINOR_VERSION . ($package->getVersion() !== null ? $package->getVersion()->toString() : ''));
    }
    /**
     * method
     * 
     * 
     * @return string
     */
    private static function strReplace(array $values, string $string) : string
    {
        foreach ($values as $key => $value) {
            $string = str_replace('{$' . $key . '}', $value, $string);
        }
        return $string;
    }
    private $package = null;
    private $includePath = null;
    private $cache = [];
    private $newEntries = false;
    private $deploymentId = '';
    /**
     * method
     * 
     * 
     * @return mixed
     */
    protected function __construct(IPackage $package, string $includePath)
    {
        $this->package = $package;
        $this->includePath = $includePath;
        $this->cache = [];
        $this->newEntries = false;
        $this->deploymentId = static::createDeploymentId($package, $includePath);
        //echo "AUTOLOADER: " . $this->deploymentId . "\n";
        if ($package->isCacheEnabled()) {
            $this->loadCache();
        }
        if ($package->isCacheEnabled() || $package->isCacheEnabled() && defined(Package::ION_AUTOLOAD_CACHE_DEBUG) && constant(Package::ION_AUTOLOAD_CACHE_DEBUG) === true) {
            $self = $this;
            register_shutdown_function(function () use($self) {
                $self->saveCache();
            });
        }
    }
    /**
     * method
     * 
     * @return string
     */
    private function getConstantName() : string
    {
        return static::CACHE_CONSTANT_PREFIX . $this->getDeploymentId();
    }
    /**
     * method
     * 
     * @return string
     */
    public function getDeploymentId() : string
    {
        return $this->deploymentId;
    }
    /**
     * method
     * 
     * @return IPackage
     */
    public function getPackage() : IPackage
    {
        return $this->package;
    }
    /**
     * method
     * 
     * @return string
     */
    public function getIncludePath() : string
    {
        return $this->includePath;
    }
    /**
     * method
     * 
     * 
     * @return ?string
     */
    protected abstract function loadClass(string $className) : ?string;
    /**
     * method
     * 
     * 
     * @return bool
     */
    public final function load(string $className) : bool
    {
        // This shouldn't be necessary?
        //if(class_exists($className, false) || interface_exists($className, false) || trait_exists($className, false)) {
        //	return;
        //}
        //echo("[CLASSNAME: " . $className . "] [PROJECT: " . $this->getPackage()->getProject() . "] [CACHE ENABLED:" . $this->getPackage()->isCacheEnabled() . "]\n");
        if ($this->getPackage()->isCacheEnabled()) {
            if ($this->hasCacheEntry($className)) {
                $path = $this->getCacheEntry($className)['path'];
                if (file_exists($path)) {
                    include $path;
                    return true;
                }
            }
            $path = $this->loadClass($className);
            if ($path !== null) {
                $this->setCacheEntry($className, $path);
                return true;
            }
            return false;
        }
        return $this->loadClass($className) !== null;
    }
    /**
     * method
     * 
     * 
     * @return bool
     */
    protected function hasCacheEntry(string $className) : bool
    {
        if (!$this->getPackage()->isCacheEnabled()) {
            return false;
        }
        if (array_key_exists($className, $this->cache)) {
            return true;
        }
        return false;
    }
    /**
     * method
     * 
     * 
     * @return ?array
     */
    protected function getCacheEntry(string $className) : ?array
    {
        if ($this->hasCacheEntry($className)) {
            return $this->cache[$className];
        }
        return null;
    }
    /**
     * method
     * 
     * 
     * @return void
     */
    protected function setCacheEntry(string $className, string $path) : void
    {
        if (!$this->hasCacheEntry($className)) {
            $this->newEntries = true;
        }
        $this->cache[$className] = ['path' => $path];
    }
    /**
     * method
     * 
     * @return void
     */
    public function saveCache() : void
    {
        if ($this->newEntries || $this->getPackage()->isCacheEnabled() && defined(Package::ION_AUTOLOAD_CACHE_DEBUG) && constant(Package::ION_AUTOLOAD_CACHE_DEBUG)) {
            $funcName = static::CACHE_FUNCTION_NAME_PREFIX . '_' . $this->getDeploymentId();
            $data = self::strReplace(['php_version' => 'for PHP version ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . ' ', 'pkg_version' => $this->getPackage()->getVersion() !== null ? 'and package version ' . $this->getPackage()->getVersion()->toString() . ', ' : '', 'time' => strftime('%c'), 'pkg_constant' => $this->getConstantName()], static::CACHE_HEADER) . 'function &' . $funcName . '()' . (PHP_MAJOR_VERSION >= 7 ? ': array' : '') . " {\n\$array = " . var_export($this->cache, true) . ";\nreturn \$array;\n}";
            if (is_dir($this->getIncludePath())) {
                file_put_contents($this->getIncludePath() . static::createCacheFilename($this->getDeploymentId()), $data);
            }
            $this->newEntries = false;
        }
    }
    /**
     * method
     * 
     * @return bool
     */
    public function loadCache() : bool
    {
        $path = $this->getIncludePath() . static::createCacheFilename($this->getDeploymentId());
        $this->newEntries = false;
        if (file_exists($path)) {
            if (!defined($this->getConstantName())) {
                define($this->getConstantName(), true);
            }
            include_once $path;
            $funcName = static::CACHE_FUNCTION_NAME_PREFIX . '_' . $this->getDeploymentId();
            if (!function_exists($funcName)) {
                return false;
            }
            $this->cache = $funcName();
            return true;
        }
        return false;
    }
}