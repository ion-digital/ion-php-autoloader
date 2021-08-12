<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace ion;

/**
 * Description of SemVer
 *
 * @author Justus
 */
class SemVer implements SemVerInterface {
        
    public static function create(
            
        int $major = 0, 
        int $minor = 0, 
        int $patch = 0, 
        string $release = null, 
        array $buildData = null

    ): SemVerInterface {
        
        return new static($major, $minor, $patch, $release, $buildData);
    }
    
    public static function parse(string $string): ?SemVerInterface {

        $tokens = [];
		
        $pos = strpos($string, '+');
        
        if($pos !== false) {
            
            $tmp = explode('.', substr($string, $pos + 1));
            
            if(count($tmp) > 0) {
                
                $tokens['build'] = $tmp;
                $string = substr($string, 0, $pos);
            }
        } 		
		
        
        $pos = strpos($string, '-');
        
        if($pos !== false) {
            
            $tmp = substr($string, $pos + 1);
            
            if(strlen($tmp) > 0) {
                
                $tokens['release'] = $tmp;
                $string = substr($string, 0, $pos);
            }
        } 
		
        if(strpos(strtolower($string), 'v') === 0) {
            
            $string = substr($string, 1);
        }

        $tmp = explode('.', $string);
        
        if(count($tmp) > 0) {
            
            $tokens['version'] = $tmp;
        }

        $tokens = array_reverse($tokens);

        if(array_key_exists('version', $tokens)) {

            $version = $tokens['version'];

            $major = 0;
            $minor = 0;
            $patch = 0;
            $release = null;
            $buildData = [];

            if(count($version) > 0) {
                
                if(!is_numeric($version[0])) {
                    
                    return null;
                }
                
                $major = intval($version[0]);
            } 

            if(count($version) > 1) {
                
                if(!is_numeric($version[1])) {
                    
                    return null;                    
                }                
                
                $minor = intval($version[1]);
            } 

            if(count($version) > 2) {
                
                if(!is_numeric($version[2])) {
                    
                    return null;
                }                
                
                $patch = intval($version[2]);
            }


            if(array_key_exists('release', $tokens)) {
                
                $release = $tokens['release'];
            }

            if(array_key_exists('build', $tokens)) {
                
                $buildData = $tokens['build'];
            }	

            return new SemVer($major, $minor, $patch, $release, $buildData);
        }	

        return null;
    }
	
    public static function parsePackageJson(string $data): ?SemVerInterface {
		
        $major = 0;
        $minor = 0;
        $patch = 1;
        $release = null;
        $buildData = null;                 

        $json = json_decode($data, true);  

        if($json !== null) {

            if(isset($json['major'])) {
                $major = intval($json['major']);
            }

            if(isset($json['minor'])) {
                $minor = intval($json['minor']);
            }

            if(isset($json['patch'])) {
                $patch = intval($json['patch']);
            }    

            if(isset($json['release'])) {
                $release = $json['release'];
            }  

            if(isset($json['build'])) {
                foreach(array_values($json['build']) as $value) {
                    $buildData[] = $value;
                }
            }

            return SemVer::create($major, $minor, $patch, $release, $buildData);
        }
        

        return null;
    }

    public static function parseComposerJson(string $data): ?SemVerInterface {

        $json = json_decode($data, true);                 

        if($json !== null) {

            if(isset($json['version'])) {                
                return SemVer::parse($json['version']);
            }
        }
        

        return null;		
    }

    
    private $major = null;
    private $minor = null;
    private $patch = null;
    private $release = null;
    private $build = [];
    
    /**
     * 
     * Instance constructor.
     * 
     * @param int $major The major version component.
     * @param int $minor The minor version component.
     * @param int $patch The patch version component.
     * 
     * @return void
     * 
     */    
    
    public function __construct(
            
        int $major = 0, 
        int $minor = 0, 
        int $patch = 0, 
        string $release = null, 
        array $buildData = null

    ) {
        
       $this->major = $major;
       $this->minor = $minor;
       $this->patch = $patch;
       $this->release = $release;
       $this->build = ($buildData === null ? [] : $buildData);
    }
    
    /**
     * 
     * Get the major version component.
     * 
     * @return int Returns the major version component.
     * 
     */    
    
    public function getMajor(): int {
        
        return $this->major;
    }
    
    /**
     * 
     * Get the minor version component.
     * 
     * @return int Returns the minor version component.
     * 
     */    
    
    public function getMinor(): int {
        
        return $this->minor;
    }
    
    /**
     * 
     * Get the patch version component.
     * 
     * @return int Returns the patch version component.
     * 
     */    
    
    public function getPatch(): int {
        
        return $this->patch;
    }
    
    /**
     * 
     * Get the release version component.
     * 
     * @return int Returns the patch version component.
     * 
     */        
    
    public function getRelease(): ?string {
        
        return $this->release;
    }
    
    /**
     * 
     * Get the build data version component.
     * 
     * @return int Returns the patch version component.
     * 
     */        
    
    public function getBuildData(): array {
        
        return $this->build;
    }
    
    /**
     * 
     * Get the version as a string.
     * 
     * @return string Return the version as a string.
     * 
     */    
    
    public function toString(): string {
        
        $string = join('.', [$this->getMajor(), $this->getMinor(), $this->getPatch()]);
        
        if($this->getRelease() !== null) {
            $string .= '-' . $this->getRelease();
        }
        
        if(count($this->getBuildData()) > 0) {
            $string .= '+' . join('.', $this->getBuildData());
        }          
        
        return $string;
    }
    
    /**
     * 
     * Get the version as a VCS tag (e.g: v0.0.0)
     * 
     * @return string The version as a VCS tag.
     * 
     */    
    
    public function toVcsTag(): string {
        
        return 'v' . $this->toString();
    }
    
    /**
     * 
     * Get the version as an array.
     * 
     * @return array Return the version as an array.
     * 
     */    
    
    public function toArray(): array {
        
        $array = [
            
            $this->getMajor(),
            $this->getMinor(),
            $this->getPatch()
        ];
        
        if($this->getRelease() !== null) {
            
            $array[] = $this->getRelease();
        }
        
        if(count($this->getBuildData()) > 0) {
            
            $array[] = $this->getBuildData();
        }        
        
        return $array;
    }
    
    public function __toString(): string {
        
        return $this->toString();
    }
    
    /**
     * 
     * Checks to see if this version is higher than the specified version.
     * 
     * @param SemVerInterface $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is higher, __false__ if not.
     * 
     */    
    
    public function isHigherThan(SemVerInterface $semVer): bool {
        
        if($this->getMajor() > $semVer->getMajor()) {
            return true;
        }
        
        if($this->getMinor() > $semVer->getMinor()) {
            
            if($this->getMajor() === $semVer->getMajor()) {
                
                return true;
            }            
            
        }        
        
        if($this->getPatch() > $semVer->getPatch()) { 
            
            if($this->getMajor() === $semVer->getMajor() && $this->getMinor() === $semVer->getMinor()) {
                
                return true;
            }
        }           
        
        return false;
        
    }
    
    /**
     * 
     * Checks to see if this version is lower than the specified version.
     * 
     * @param SemVerInterface $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is lower, __false__ if not.
     * 
     */    
    
    public function isLowerThan(SemVerInterface $semVer): bool {        
        
        if($this->getMajor() < $semVer->getMajor()) {
            
            return true;
        }
        
        if($this->getMinor() < $semVer->getMinor()) {
            
            if($this->getMajor() === $semVer->getMajor()) {  
                
                return true;
            }            
            
        }        
        
        if($this->getPatch() < $semVer->getPatch()) {
            
            if($this->getMajor() === $semVer->getMajor() && $this->getMinor() === $semVer->getMinor()) {
                
                return true;
            }
        }           
        
        return false;      
    }
    
    /**
     * 
     * Checks to see if this version is equal to the specified version.
     * 
     * @param SemVerInterface $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is equal, __false__ if not.
     * 
     */    
    
    public function isEqualTo(SemVerInterface $semVer): bool {
        
        if($this->getMajor() !== $semVer->getMajor()) {
            
            return false;
        }
        
        if($this->getMinor() !== $semVer->getMinor()) {
            
            return false;
        }        
        
        if($this->getPatch() !== $semVer->getPatch()) {
            
            return false;
        }           
        
        return true;         
        
    }
    
}
