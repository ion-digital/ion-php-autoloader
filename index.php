<?php

/*
 * See license information at the package root in LICENSE.md
 */

$bootstrap = realpath(__DIR__ . "/vendor/ion/packaging/bootstrap.php") ?: realpath(__DIR__ . "/../packaging/bootstrap.php");

if(!empty($bootstrap))
    require_once($bootstrap);

\Ion\Package::create("ion", "autoloader", function($package) {

    spl_autoload_register(function( /* string */ $className) {
    
        $dirs = [
            
            'source/classes',
            'source/interfaces',
            'source/traits',
            'builds/' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            'builds/' . PHP_MAJOR_VERSION,
        ];
    
        foreach($dirs as $dir) {
        
            $classPath = __DIR__ . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
            
            $classPath = realPath($classPath);
            
            if (file_exists($classPath)) {
                
                require_once($classPath);
                break;
            }
        }
        
    }, true, true);
    
    return null;

}, __FILE__);

