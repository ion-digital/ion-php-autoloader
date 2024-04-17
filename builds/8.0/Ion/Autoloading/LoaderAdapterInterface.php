<?php
namespace Ion\Autoloading;

use Ion\Autoloading\AutoloaderInterface;
interface LoaderAdapterInterface
{
    function getDeploymentId() : string;
    function getAutoloader() : AutoloaderInterface;
    function getIncludePath() : string;
    function load(string $className) : bool;
    function saveCache() : void;
    function loadCache() : bool;
}