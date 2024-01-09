<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace ion\AutoLoading;

/**
 * Description of PackageTest
 *
 * @author Justus
 */

use \Ion\Package;
use \Ion\PackageInterface;
use \Ion\ISemVer;
use \Ion\SemVer;
use \Ion\AutoLoading\AutoLoader;
use \Ion\AutoLoading\AutoLoaderException;
use \Ion\AutoLoading\LoaderAdapter;
use PHPUnit\Framework\TestCase;

class AutoLoaderTest extends TestCase {
    
    const TEST_PACKAGE_VENDOR = 'xyz';
    const TEST_PACKAGE_PROJECT = 'package';
    const TEST_PACKAGE = self::TEST_PACKAGE_VENDOR . '/' . self::TEST_PACKAGE_PROJECT;
    
    const TEST_PACKAGE_PROJECT_1 = self::TEST_PACKAGE_PROJECT . '_1';
    const TEST_PACKAGE_PROJECT_2 = self::TEST_PACKAGE_PROJECT . '_2';    
    const TEST_PACKAGE_PROJECT_3 = self::TEST_PACKAGE_PROJECT . '_3'; 
    const TEST_PACKAGE_PROJECT_4 = self::TEST_PACKAGE_PROJECT . '_4'; 
    const TEST_PACKAGE_PROJECT_5 = self::TEST_PACKAGE_PROJECT . '_5'; 
    const TEST_PACKAGE_PROJECT_6 = self::TEST_PACKAGE_PROJECT . '_6'; 
    const TEST_PACKAGE_PROJECT_7 = self::TEST_PACKAGE_PROJECT . '_7'; 
    const TEST_PACKAGE_PROJECT_8 = self::TEST_PACKAGE_PROJECT . '_8'; 
    const TEST_PACKAGE_PROJECT_9 = self::TEST_PACKAGE_PROJECT . '_9';  
    const TEST_PACKAGE_PROJECT_10 = self::TEST_PACKAGE_PROJECT . '_10'; 
    
    const TEST_PACKAGE_1 = self::TEST_PACKAGE_VENDOR . '/' . self::TEST_PACKAGE_PROJECT_1;
    const TEST_PACKAGE_2 = self::TEST_PACKAGE_VENDOR . '/' . self::TEST_PACKAGE_PROJECT_2;
    
    const AUTO_LOADER_PROJECT_DIR = '../../data/';
    const ENTRY_FILENAME = 'root.txt';
    
    const SOURCE_DIRECTORY = './a/';
    const EXTRA_DIRECTORY_1 = './b/';
    const EXTRA_DIRECTORY_2 = './c/';
    const NON_EXISTENT_DIRECTORY = './non_existent/';
    
    const MAJOR_VERSION = 1;
    const MINOR_VERSION = 2;
    const PATCH_VERSION = 3;
    
    private static function createProjectRootFile() {
        
        return( realpath(__DIR__ . DIRECTORY_SEPARATOR . self::AUTO_LOADER_PROJECT_DIR) . DIRECTORY_SEPARATOR . self::ENTRY_FILENAME );
    }

    private static function createPackage(string $project, bool $createFileName = true) {
        
        // $version = null;
        
        // if($createVersion === true)
        //     $version = new SemVer(self::MAJOR_VERSION, self::MINOR_VERSION, self::PATCH_VERSION);

        return Package::create(
            
            self::TEST_PACKAGE_VENDOR, 
            $project,
            function($package) {

                return;
            },
            self::createProjectRootFile()
        );

        return;      
    }    
    
    private static function createAutoLoader(string $project, bool $debug = null, bool $cache = null, array $loaders = null, bool $createFileName = true) {

        return AutoLoader::create(
            
            static::createPackage($project, $createFileName), 
            [ self::SOURCE_DIRECTORY ], 
            [            
                self::EXTRA_DIRECTORY_1,
                self::EXTRA_DIRECTORY_2,
                self::NON_EXISTENT_DIRECTORY
            ], 
            $debug, 
            $cache, 
            $loaders
        );
    }


    
    public function testCreate() {
        
        $al = static::createAutoLoader(self::TEST_PACKAGE_PROJECT_1, true, false);
        
        $this->assertEquals(true, $al->isDebugEnabled());
        $this->assertEquals(false, $al->isCacheEnabled());
        $this->assertEquals(3, count($al->getAdditionalPaths()));
        $this->assertEquals(1, count($al->getSearchPaths()));
           
        $al->destroy();       
    }
    
    public function testAdapters() {

        $this->expectException(AutoLoaderException::class);        
        self::createAutoLoader(self::TEST_PACKAGE_PROJECT_4, true, false, [ 'a_non_existent_class' ]);         
    }
    
    public function testLoad() {
        
        $al = self::createAutoLoader(self::TEST_PACKAGE_PROJECT_5, false, false);        
        
        $this->assertEquals(false, $al->isDebugEnabled());
        $this->assertEquals(false, $al->isCacheEnabled());
        
        $this->assertEquals(false, class_exists('\\Tests\\TestClass1', false));
        $testClass1 = new \Tests\TestClass1();
        $this->assertEquals(true, class_exists('\\Tests\\TestClass1', false));        
        
        $this->assertEquals(false, class_exists('\\TestClass3', false));
        $testClass3 = new \TestClass3();
        $this->assertEquals(true, class_exists('\\TestClass3', false));     
        
        $al->destroy();
    }
    
    public function testCache() {
        
        $al = self::createAutoLoader(self::TEST_PACKAGE_PROJECT_6, false, true);

        $this->assertEquals(false, $al->isDebugEnabled());
        $this->assertEquals(true, $al->isCacheEnabled());
        
        $this->assertEquals(false, class_exists('\\Tests\\TestClass2', false));
        $testClass2 = new \Tests\TestClass2();
        $this->assertEquals(true, class_exists('\\Tests\\TestClass2', false));        
        
        $this->assertEquals(false, class_exists('\\TestClass4', false));
        $testClass4 = new \TestClass4();
        $this->assertEquals(true, class_exists('\\TestClass4', false));
                
        $a = AutoLoader::createSearchPath($al->getPackage(), self::SOURCE_DIRECTORY);
        $b = AutoLoader::createSearchPath($al->getPackage(), self::EXTRA_DIRECTORY_1);
        $c = AutoLoader::createSearchPath($al->getPackage(), self::EXTRA_DIRECTORY_2);

        $this->assertEquals(true, $a !== null);
        $this->assertEquals(true, $b !== null);
        $this->assertEquals(true, $c !== null);
        
        $this->assertEquals(true, in_array($a, $al->getSearchPaths(false)));
        $this->assertEquals(true, in_array($b, $al->getSearchPaths(false)));
        $this->assertEquals(true, in_array($c, $al->getSearchPaths(false)));

        $aId = LoaderAdapter::createDeploymentId($al->getPackage(), $a);
        $bId = LoaderAdapter::createDeploymentId($al->getPackage(), $b);
        $cId = LoaderAdapter::createDeploymentId($al->getPackage(), $c);                      
        
        $a = $a . LoaderAdapter::createCacheFilename($aId);
        $b = $b . LoaderAdapter::createCacheFilename($bId);
        $c = $c . LoaderAdapter::createCacheFilename($cId);        
             
        //die("\n\n$a\n\n");
        
        $al->flushCache();
        
        $this->assertEquals(true, file_exists($a));
        $this->assertEquals(false, file_exists($b));
        $this->assertEquals(true, file_exists($c));
        
        $al->destroy();
    }
    
    public function testDebug() {
		        
        $al1 = self::createAutoLoader(self::TEST_PACKAGE_PROJECT_7, true, false);     
        
        $this->assertEquals(true, $al1->isDebugEnabled());
        $this->assertEquals(false, $al1->isCacheEnabled());        
        $this->assertEquals(3, count($al1->getAdditionalPaths()));
        $this->assertEquals(1, count($al1->getSearchPaths()));        
        
        $al2 = self::createAutoLoader(self::TEST_PACKAGE_PROJECT_8, false, false);      
        
        $this->assertEquals(false, $al2->isDebugEnabled());     
        $this->assertEquals(false, $al2->isCacheEnabled());
        $this->assertEquals(3, count($al2->getAdditionalPaths()));
        $this->assertEquals(3, count($al2->getSearchPaths()));
        
        $al1->destroy();
        $al2->destroy();
    }
}
