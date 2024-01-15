<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace Ion\Autoloading;

/**
 * Description of PackageTest
 *
 * @author Justus
 */

use \Ion\Package;
use \Ion\PackageInterface;
use \Ion\SemVer;
use \Ion\Autoloading\Autoloader;
use \Ion\Autoloading\AutoloaderException;
use \Ion\Autoloading\LoaderAdapter;
use PHPUnit\Framework\TestCase;

class AutoloaderTest extends TestCase {
    
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
    
    const AUTO_LOADER_PROJECT_DIR = '../../../data/';
    const ENTRY_FILENAME = 'root.txt';
    
    const SOURCE_DIRECTORY = './a/';
    const EXTRA_DIRECTORY_1 = './b/';
    const EXTRA_DIRECTORY_2 = './c/';
    const NON_EXISTENT_DIRECTORY = './non_existent/';
    
    const MAJOR_VERSION = 1;
    const MINOR_VERSION = 2;
    const PATCH_VERSION = 3;
    
    private static function createProjectFile(string $filename) {

        return( realpath(__DIR__ . DIRECTORY_SEPARATOR . self::AUTO_LOADER_PROJECT_DIR) . DIRECTORY_SEPARATOR . $filename );
    }

    private static function createProjectRootFile() {

        return self::createProjectFile(self::ENTRY_FILENAME);
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
    
    private static function createAutoloader(string $project, bool $debug = null, bool $cache = null, array $loaders = null, bool $createFileName = true) {

        $pkg = static::createPackage($project, $createFileName);

        return Autoloader::create(
            
            $pkg, 
            [ self::SOURCE_DIRECTORY ], 
            [            
                self::EXTRA_DIRECTORY_1,
                self::EXTRA_DIRECTORY_2,
                self::NON_EXISTENT_DIRECTORY
            ], 
            new AutoloaderSettings($pkg, $cache ?? false, $debug ?? false),
            $loaders
        );
    }


    
    public function testCreate() {

        $al = static::createAutoloader(self::TEST_PACKAGE_PROJECT_1, true, false);
        
        $this->assertTrue($al->getSettings()->isDebugEnabled());
        $this->assertFalse($al->getSettings()->isCacheEnabled());
        $this->assertEquals(3, count($al->getAdditionalPaths()));        
        $this->assertEquals(1, count($al->getDevelopmentPaths()));
        $this->assertEquals(1, count($al->getSearchPaths())); // Debug === true        

        $al->destroy();       
    }
    
    public function testAdapters() {

        $this->expectException(AutoloaderException::class);        
        self::createAutoloader(self::TEST_PACKAGE_PROJECT_4, true, false, [ 'a_non_existent_class' ]);         
    }
    
    public function testLoad() {
        
        $al = self::createAutoloader(self::TEST_PACKAGE_PROJECT_5, false, false);        
        
// var_dump($al->getSettings());
// exit;

        $this->assertFalse($al->getSettings()->isDebugEnabled());
        $this->assertFalse($al->getSettings()->isCacheEnabled());
        
        $this->assertFalse(class_exists('\\Tests\\TestClass1', false));
        $testClass1 = new \Tests\TestClass1();
        $this->assertTrue(class_exists('\\Tests\\TestClass1', false));        
        
        $this->assertFalse(class_exists('\\TestClass3', false));
        $testClass3 = new \TestClass3();
        $this->assertTrue(class_exists('\\TestClass3', false));     
        
        $al->destroy();
    }
    
    public function testCache() {
        
        $al = self::createAutoloader(self::TEST_PACKAGE_PROJECT_6, false, true);

        $this->assertFalse($al->getSettings()->isDebugEnabled());
        $this->assertTrue($al->getSettings()->isCacheEnabled());
        
        $this->assertFalse(class_exists('\\Tests\\TestClass2', false));
        $testClass2 = new \Tests\TestClass2();
        $this->assertTrue(class_exists('\\Tests\\TestClass2', false));        
        
        $this->assertFalse(class_exists('\\TestClass4', false));
        $testClass4 = new \TestClass4();
        $this->assertTrue(class_exists('\\TestClass4', false));
                
        $a = Autoloader::createSearchPath($al->getPackage(), self::SOURCE_DIRECTORY);
        $b = Autoloader::createSearchPath($al->getPackage(), self::EXTRA_DIRECTORY_1);
        $c = Autoloader::createSearchPath($al->getPackage(), self::EXTRA_DIRECTORY_2);

        $this->assertNotNull($a);
        $this->assertNotNull($b);
        $this->assertNotNull($c);
        
        $this->assertTrue(in_array($a, $al->getSearchPaths(false)));
        $this->assertTrue(in_array($b, $al->getSearchPaths(false)));
        $this->assertTrue(in_array($c, $al->getSearchPaths(false)));

        $aId = LoaderAdapter::createDeploymentId($al->getPackage(), $a);
        $bId = LoaderAdapter::createDeploymentId($al->getPackage(), $b);
        $cId = LoaderAdapter::createDeploymentId($al->getPackage(), $c);                      
        
        $a = $a . LoaderAdapter::createCacheFilename($aId);
        $b = $b . LoaderAdapter::createCacheFilename($bId);
        $c = $c . LoaderAdapter::createCacheFilename($cId);        
             
        //die("\n\n$a\n\n");
        
        $al->flushCache();
        
        $this->assertTrue(file_exists($a));
        $this->assertFalse(file_exists($b));
        $this->assertTrue(file_exists($c));
        
        $al->destroy();
    }
    
    public function testDebug() {
		        
        $al1 = self::createAutoloader(self::TEST_PACKAGE_PROJECT_7, true, false);     
        
        $this->assertTrue($al1->getSettings()->isDebugEnabled());
        $this->assertFalse($al1->getSettings()->isCacheEnabled());        
        $this->assertEquals(3, count($al1->getAdditionalPaths()));        
        $this->assertEquals(1, count($al1->getDevelopmentPaths()));      
        $this->assertEquals(1, count($al1->getSearchPaths())); // Debug === true        
        
        $al2 = self::createAutoloader(self::TEST_PACKAGE_PROJECT_8, false, false);      
        
        $this->assertFalse($al2->getSettings()->isDebugEnabled());     
        $this->assertFalse($al2->getSettings()->isCacheEnabled());
        $this->assertEquals(3, count($al2->getAdditionalPaths()));        
        $this->assertEquals(3, count($al2->getSearchPaths()));
        $this->assertEquals(1, count($al2->getDevelopmentPaths()));
        
        $al1->destroy();
        $al2->destroy();
    }

    public function testSettings() {

        $pkg1 = static::createPackage(self::TEST_PACKAGE_PROJECT_9, static::createProjectRootFile());

        $settings1 = new AutoloaderSettings($pkg1);

        $this->assertTrue($settings1->isCacheEnabled());
        $this->assertFalse($settings1->isDebugEnabled());

        $this->assertTrue(AutoloaderSettings::get($pkg1)->isCacheEnabled());
        $this->assertFalse(AutoloaderSettings::get($pkg1)->isDebugEnabled());        

        $settings1->destroy();

        $settings2 = new AutoloaderSettings($pkg1, false, false);

        $this->assertFalse($settings2->isCacheEnabled());
        $this->assertFalse($settings2->isDebugEnabled());

        $this->assertFalse(AutoloaderSettings::get($pkg1)->isCacheEnabled());
        $this->assertFalse(AutoloaderSettings::get($pkg1)->isDebugEnabled());  

        $settings2->destroy();

        $settings3 = new AutoloaderSettings($pkg1, true, false);

        $this->assertTrue($settings3->isCacheEnabled());
        $this->assertFalse($settings3->isDebugEnabled());

        $this->assertTrue(AutoloaderSettings::get($pkg1)->isCacheEnabled());
        $this->assertFalse(AutoloaderSettings::get($pkg1)->isDebugEnabled());          

        $settings3->destroy();

        $settings4 = new AutoloaderSettings($pkg1, true, true);

        $this->assertTrue($settings4->isCacheEnabled());
        $this->assertTrue($settings4->isDebugEnabled());

        $this->assertTrue(AutoloaderSettings::get($pkg1)->isCacheEnabled());
        $this->assertTrue(AutoloaderSettings::get($pkg1)->isDebugEnabled());  

        $settings4->destroy();

        $settings5 = new AutoloaderSettings($pkg1, false, true);

        $this->assertFalse($settings5->isCacheEnabled());
        $this->assertTrue($settings5->isDebugEnabled());

        $this->assertFalse(AutoloaderSettings::get($pkg1)->isCacheEnabled());
        $this->assertTrue(AutoloaderSettings::get($pkg1)->isDebugEnabled());          

        $settings5->destroy();

        $pkg2 = static::createPackage(self::TEST_PACKAGE_PROJECT_10, static::createProjectRootFile());
        $this->assertTrue(AutoloaderSettings::exists($pkg2));

        $settings6 = AutoloaderSettings::load($pkg2);

        $this->assertFalse($settings6->isCacheEnabled());
        $this->assertTrue($settings6->isDebugEnabled());

        $settings7 = AutoloaderSettings::get($pkg2);

        $this->assertFalse($settings7->isCacheEnabled());
        $this->assertTrue($settings7->isDebugEnabled());        

        $pkg1->destroy();

        $this->expectException(AutoloaderSettingsException::class);
        $settings8 = new AutoloaderSettings($pkg2, false, true);

        $pkg2->destroy();
    }
}
