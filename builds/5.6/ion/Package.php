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
use ion\ISemVer;
use ion\SemVer;
use ion\Packages\PackageException;
use ion\Packages\Adapters\Psr0Loader;
use ion\Packages\Adapters\Psr4Loader;
use ion\IConfiguration;
use ion\Configuration;

final class Package implements IPackage
{
    const PHP_VERSION_SEPARATOR = '.';
    const COMPOSER_FILENAME = 'composer.json';
    const ION_PACKAGE_CONFIGURATION_FILENAME = "autoloader.json";
    const ION_PACKAGE_VERSION_FILENAME = 'version.json';
    const ION_AUTOLOAD_CACHE = 'ION_AUTOLOAD_CACHE';
    const ION_AUTOLOAD_CACHE_DEBUG = 'ION_AUTOLOAD_CACHE_DEBUG';
    const ION_PACKAGE_IGNORE_VERSION = 'ION_PACKAGE_IGNORE_VERSION';
    const ION_PACKAGE_DEBUG = 'ION_PACKAGE_DEBUG';
    const ION_PACKAGE_IGNORE_CONFIGURATION = 'ION_PACKAGE_IGNORE_CONFIGURATION';
    private static $instances = [];
    /**
     * method
     * 
     * 
     * @return IPackage
     */
    
    public static function create($vendor, $project, array $developmentPaths, array $additionalPaths = null, $projectRoot = null, ISemVer $version = null, $enableDebug = null, $enableCache = null, array $loaderClassNames = null)
    {
        return new static($vendor, $project, $developmentPaths, $additionalPaths, $projectRoot, $version, $enableDebug, $enableCache, $loaderClassNames);
    }
    
    /**
     * method
     * 
     * 
     * @return ?string
     */
    
    public static function createSearchPath(IPackage $package, $path)
    {
        $includePath = trim($package->getProjectRoot(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        //echo $includePath . "\n";
        if (DIRECTORY_SEPARATOR === '/') {
            $includePath = DIRECTORY_SEPARATOR . $includePath;
        }
        $includePath = realpath($includePath);
        return $includePath === false ? null : $includePath . DIRECTORY_SEPARATOR;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public static function getInstances()
    {
        return static::$instances;
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function hasInstance($vendorName, $projectName)
    {
        return (bool) array_key_exists($vendorName . '/' . $projectName, static::$instances);
    }
    
    /**
     * method
     * 
     * 
     * @return ?IPackage
     */
    
    public static function getInstance($vendorName, $projectName)
    {
        if (!static::hasInstance($vendorName, $projectName)) {
            return null;
        }
        return static::$instances[$vendorName . '/' . $projectName];
    }
    
    /**
     * method
     * 
     * 
     * @return void
     */
    
    protected static function destroyInstance(self $instance)
    {
        unset(static::$instances[$instance->getName()]);
    }
    
    /**
     * method
     * 
     * 
     * @return void
     */
    
    protected static function registerInstance(self $instance)
    {
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
     * method
     * 
     * 
     * @return string
     */
    
    public static function getCallingDirectory($back = 1)
    {
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
    /**
     * method
     * 
     * 
     * @return mixed
     */
    
    protected function __construct($vendor, $project, array $sourcePaths, array $additionalPaths = null, $projectRoot = null, ISemVer $version = null, $enableDebug = null, $enableCache = null, array $loaderClassNames = null)
    {
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
        if (empty($tmp)) {
            throw new PackageException("Project root / entry '{$projectRoot}' for package '{$vendor}/{$project}' is invalid.");
        }
        if (!is_dir($tmp)) {
            $this->projectEntry = $tmp;
            $this->projectRoot = pathinfo($tmp . DIRECTORY_SEPARATOR, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        } else {
            $this->projectRoot = $tmp . DIRECTORY_SEPARATOR;
        }
        $this->config = $this->loadConfiguration();
        // Enable source/debug mode?
        $this->enableDebug = null;
        if ($enableDebug === null) {
            if ($this->isDependency() === false) {
                if (defined(static::ION_PACKAGE_DEBUG)) {
                    $this->enableDebug = (bool) constant(static::ION_PACKAGE_DEBUG) === true;
                }
            }
            if ($this->enableDebug === null && $this->hasDebugIndicator()) {
                $this->enableDebug = true;
            }
            if ($this->enableDebug === null && $this->hasRepository() && $this->isDependency() === false) {
                $this->enableDebug = true;
            }
            if ($this->enableDebug === null) {
                $this->enableDebug = false;
            }
        } else {
            $this->enableDebug = $enableDebug;
        }
        // Use the cache?
        $this->enableCache = null;
        if ($enableCache === null) {
            if (defined(static::ION_AUTOLOAD_CACHE)) {
                $this->enableCache = (bool) constant(static::ION_AUTOLOAD_CACHE) === true;
            }
            if ($this->enableCache === null && $this->hasCacheIndicator()) {
                $this->enableCache = true;
            }
            if ($this->enableCache === null && $this->enableDebug === true) {
                $this->enableCache = false;
            }
            if ($this->enableCache === null) {
                $this->enableCache = true;
            }
        } else {
            $this->enableCache = $enableCache;
        }
        $this->version = $version;
        if ($this->version === null) {
            $this->version = $this->loadVersion();
        }
        $this->includePaths = $additionalPaths;
        if ($this->includePaths === null) {
            $this->includePaths = [];
        }
        $tmpPaths = $this->includePaths;
        if ($this->enableDebug) {
            $tmpPaths = [];
            // Override if 'debug' is true
        }
        // Add the dev directories at the end
        $tmpPaths = array_merge($tmpPaths, $sourcePaths);
        $this->searchPaths = [];
        foreach ($tmpPaths as $path) {
            $includePath = static::createSearchPath($this, $path);
            if ($includePath !== null) {
                $this->searchPaths[] = $includePath;
                if ($loaderClassNames === null || is_array($loaderClassNames) && count($loaderClassNames) === 0) {
                    $psr0 = Psr0Loader::class;
                    $psr4 = Psr4Loader::class;
                    $this->loaders[] = $psr0::create($this, $includePath);
                    $this->loaders[] = $psr4::create($this, $includePath);
                } else {
                    foreach ($loaderClassNames as $loaderClassName) {
                        if (!class_exists($loaderClassName)) {
                            throw new PackageException("'{$loaderClassName}' does not exist and cannot be used as an auto-loader.");
                        }
                        $this->loaders[] = $loaderClassName::create($this, $includePath);
                    }
                }
            }
        }
        static::registerInstance($this);
        $this->registerLoaders();
    }
    
    /**
     * method
     * 
     * @return ?bool
     */
    
    protected function isDependency()
    {
        // NULL = Possibly, not sure; TRUE = Definitely yes; FALSE = Definitely no.
        //.gitignore? .git? composer.json? /vendor ? version.json? .hg? .hgignore?
        if (strstr($this->projectRoot, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
            return true;
        }
        return null;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    protected function hasRepository()
    {
        $repos = ['.git', '.hg'];
        foreach ($repos as $repo) {
            if (is_dir($this->projectRoot . DIRECTORY_SEPARATOR . $repo)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    protected function hasDebugIndicator()
    {
        return $this->getConfiguration()->getSettingAsBool('debug');
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    protected function hasCacheIndicator()
    {
        return $this->getConfiguration()->getSettingAsBool('cache');
    }
    
    /**
     * method
     * 
     * @return void
     */
    
    protected function registerLoaders()
    {
        if (count($this->getHooks()) === 0) {
            try {
                $self = $this;
                foreach ($this->loaders as $index => $loader) {
                    $this->hooks[] = function ($className) use($index, $loader, $self) {
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
     * method
     * 
     * @return void
     */
    
    public function destroy()
    {
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
     * method
     * 
     * @return array
     */
    
    public function getHooks()
    {
        return $this->hooks;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getLoaders()
    {
        return $this->loaders;
    }
    
    /**
     * method
     * 
     * @return IConfiguration
     */
    
    public function getConfiguration()
    {
        if ($this->config === null) {
            $this->config = $this->loadConfiguration();
        }
        return $this->config;
    }
    
    /**
     * method
     * 
     * 
     * @return string
     */
    
    protected function getVendorRoot($includePath, $phpMajorVersion = null, $phpMinorVersion = null)
    {
        if ($phpMajorVersion !== null || $phpMajorVersion !== null && $phpMinorVersion !== null) {
            if ($phpMinorVersion !== null) {
                return $includePath . DIRECTORY_SEPARATOR . $phpMajorVersion . static::PHP_VERSION_SEPARATOR . $phpMinorVersion . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
            }
            return $includePath . DIRECTORY_SEPARATOR . $phpMajorVersion . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
        }
        return $includePath . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
    }
    
    /**
     * method
     * 
     * @return IConfiguration
     */
    
    protected function loadConfiguration()
    {
        if (defined(static::ION_PACKAGE_IGNORE_CONFIGURATION) && constant(static::ION_PACKAGE_IGNORE_CONFIGURATION) === true) {
            return new Configuration([]);
        }
        $data = null;
        $path = $this->getProjectRoot() . DIRECTORY_SEPARATOR . static::ION_PACKAGE_CONFIGURATION_FILENAME;
        if (file_exists($path)) {
            $data = file_get_contents($path);
        }
        if (empty($data)) {
            return new Configuration([]);
        }
        return Configuration::parseJson($data);
    }
    
    /**
     * method
     * 
     * @return ?ISemVer
     */
    
    protected function loadVersion()
    {
        if (defined(static::ION_PACKAGE_IGNORE_VERSION) && constant(static::ION_PACKAGE_IGNORE_VERSION) === true) {
            return null;
        }
        $path = $this->getProjectRoot() . static::ION_PACKAGE_VERSION_FILENAME;
        if (file_exists($path)) {
            $data = file_get_contents($path);
            if ($data !== false) {
                $version = SemVer::parsePackageJson($data);
                if ($version !== null) {
                    return $version;
                }
            }
        }
        $path = $this->getProjectRoot() . static::COMPOSER_FILENAME;
        if (file_exists($path)) {
            $data = file_get_contents($path);
            if ($data !== false) {
                return SemVer::parseComposerJson($data);
            }
        }
        return null;
    }
    
    /**
     * method
     * 
     * @return ?ISemVer
     */
    
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getVendor()
    {
        return $this->vendor;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getProject()
    {
        return $this->project;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getProjectRoot()
    {
        return $this->projectRoot;
    }
    
    /**
     * method
     * 
     * @return ?string
     */
    
    public function getProjectEntry()
    {
        return $this->projectEntry;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    public function isCacheEnabled()
    {
        return $this->enableCache;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    public function isDebugEnabled()
    {
        return $this->enableDebug;
    }
    
    /**
     * method
     * 
     * @return void
     */
    
    public function flushCache()
    {
        foreach ($this->loaders as $loader) {
            $loader->saveCache();
        }
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getDevelopmentPaths()
    {
        return $this->sourcePaths;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getAdditionalPaths()
    {
        return $this->includePaths;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getSearchPaths()
    {
        return $this->searchPaths;
    }

}