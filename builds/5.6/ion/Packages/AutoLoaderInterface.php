<?php
namespace ion\Packages;

use ion\PackageInterface;
use ion\Packages\IAutoLoader;
interface AutoLoaderInterface extends IAutoLoader
{
    /**
     * method
     * 
     * 
     * @return AutoLoaderInterface
     */
    static function create(PackageInterface $package, $includePath);
    /**
     * method
     * 
     * 
     * @return string
     */
    static function createCacheFilename($deploymentId);
    /**
     * method
     * 
     * 
     * @return string
     */
    static function createDeploymentId(PackageInterface $package, $includePath);
    /**
     * method
     * 
     * @return string
     */
    function getDeploymentId();
    /**
     * method
     * 
     * @return PackageInterface
     */
    function getPackage();
    /**
     * method
     * 
     * @return string
     */
    function getIncludePath();
    /**
     * method
     * 
     * 
     * @return bool
     */
    function load($className);
    /**
     * method
     * 
     * @return void
     */
    function saveCache();
    /**
     * method
     * 
     * @return bool
     */
    function loadCache();
}