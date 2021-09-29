<?php
namespace ion\Packages;

use ion\PackageInterface;
interface AutoLoaderInterface
{
    /**
     * method
     * 
     * 
     * @return AutoLoaderInterface
     */
    static function create(PackageInterface $package, string $includePath) : AutoLoaderInterface;
    /**
     * method
     * 
     * 
     * @return string
     */
    static function createCacheFilename(string $deploymentId) : string;
    /**
     * method
     * 
     * 
     * @return string
     */
    static function createDeploymentId(PackageInterface $package, string $includePath) : string;
    /**
     * method
     * 
     * @return string
     */
    function getDeploymentId() : string;
    /**
     * method
     * 
     * @return PackageInterface
     */
    function getPackage() : PackageInterface;
    /**
     * method
     * 
     * @return string
     */
    function getIncludePath() : string;
    /**
     * method
     * 
     * 
     * @return bool
     */
    function load(string $className) : bool;
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
    function loadCache() : bool;
}