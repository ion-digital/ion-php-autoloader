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
class SemVer implements ISemVer
{
    /**
     * method
     * 
     * 
     * @return ISemVer
     */
    public static function create(int $major = 0, int $minor = 0, int $patch = 0, string $release = null, array $buildData = null) : ISemVer
    {
        return new static($major, $minor, $patch, $release, $buildData);
    }
    /**
     * method
     * 
     * 
     * @return ?ISemVer
     */
    public static function parse(string $string) : ?ISemVer
    {
        $tokens = [];
        $pos = strpos($string, '+');
        if ($pos !== false) {
            $tmp = explode('.', substr($string, $pos + 1));
            if (count($tmp) > 0) {
                $tokens['build'] = $tmp;
                $string = substr($string, 0, $pos);
            }
        }
        $pos = strpos($string, '-');
        if ($pos !== false) {
            $tmp = substr($string, $pos + 1);
            if (strlen($tmp) > 0) {
                $tokens['release'] = $tmp;
                $string = substr($string, 0, $pos);
            }
        }
        if (strpos(strtolower($string), 'v') === 0) {
            $string = substr($string, 1);
        }
        $tmp = explode('.', $string);
        if (count($tmp) > 0) {
            $tokens['version'] = $tmp;
        }
        $tokens = array_reverse($tokens);
        if (array_key_exists('version', $tokens)) {
            $version = $tokens['version'];
            $major = 0;
            $minor = 0;
            $patch = 0;
            $release = null;
            $buildData = [];
            if (count($version) > 0) {
                if (!is_numeric($version[0])) {
                    return null;
                }
                $major = intval($version[0]);
            }
            if (count($version) > 1) {
                if (!is_numeric($version[1])) {
                    return null;
                }
                $minor = intval($version[1]);
            }
            if (count($version) > 2) {
                if (!is_numeric($version[2])) {
                    return null;
                }
                $patch = intval($version[2]);
            }
            if (array_key_exists('release', $tokens)) {
                $release = $tokens['release'];
            }
            if (array_key_exists('build', $tokens)) {
                $buildData = $tokens['build'];
            }
            return new SemVer($major, $minor, $patch, $release, $buildData);
        }
        return null;
    }
    /**
     * method
     * 
     * 
     * @return ?ISemVer
     */
    public static function parsePackageJson(string $data) : ?ISemVer
    {
        $major = 0;
        $minor = 0;
        $patch = 1;
        $release = null;
        $buildData = null;
        $json = json_decode($data, true);
        if ($json !== null) {
            if (isset($json['major'])) {
                $major = intval($json['major']);
            }
            if (isset($json['minor'])) {
                $minor = intval($json['minor']);
            }
            if (isset($json['patch'])) {
                $patch = intval($json['patch']);
            }
            if (isset($json['release'])) {
                $release = $json['release'];
            }
            if (isset($json['build'])) {
                foreach (array_values($json['build']) as $value) {
                    $buildData[] = $value;
                }
            }
            return SemVer::create($major, $minor, $patch, $release, $buildData);
        }
        return null;
    }
    /**
     * method
     * 
     * 
     * @return ?ISemVer
     */
    public static function parseComposerJson(string $data) : ?ISemVer
    {
        $json = json_decode($data, true);
        if ($json !== null) {
            if (isset($json['version'])) {
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
     * method
     * 
     * 
     * @return mixed
     */
    public function __construct(int $major = 0, int $minor = 0, int $patch = 0, string $release = null, array $buildData = null)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->release = $release;
        $this->build = $buildData === null ? [] : $buildData;
    }
    /**
     * method
     * 
     * @return int
     */
    public function getMajor() : int
    {
        return $this->major;
    }
    /**
     * method
     * 
     * @return int
     */
    public function getMinor() : int
    {
        return $this->minor;
    }
    /**
     * method
     * 
     * @return int
     */
    public function getPatch() : int
    {
        return $this->patch;
    }
    /**
     * method
     * 
     * @return ?string
     */
    public function getRelease() : ?string
    {
        return $this->release;
    }
    /**
     * method
     * 
     * @return array
     */
    public function getBuildData() : array
    {
        return $this->build;
    }
    /**
     * method
     * 
     * @return string
     */
    public function toString() : string
    {
        $string = join('.', [$this->getMajor(), $this->getMinor(), $this->getPatch()]);
        if ($this->getRelease() !== null) {
            $string .= '-' . $this->getRelease();
        }
        if (count($this->getBuildData()) > 0) {
            $string .= '+' . join('.', $this->getBuildData());
        }
        return $string;
    }
    /**
     * method
     * 
     * @return string
     */
    public function toVcsTag() : string
    {
        return 'v' . $this->toString();
    }
    /**
     * method
     * 
     * @return array
     */
    public function toArray() : array
    {
        $array = [$this->getMajor(), $this->getMinor(), $this->getPatch()];
        if ($this->getRelease() !== null) {
            $array[] = $this->getRelease();
        }
        if (count($this->getBuildData()) > 0) {
            $array[] = $this->getBuildData();
        }
        return $array;
    }
    /**
     * method
     * 
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
    /**
     * method
     * 
     * 
     * @return bool
     */
    public function isHigherThan(ISemVer $semVer) : bool
    {
        if ($this->getMajor() > $semVer->getMajor()) {
            return true;
        }
        if ($this->getMinor() > $semVer->getMinor()) {
            if ($this->getMajor() === $semVer->getMajor()) {
                return true;
            }
        }
        if ($this->getPatch() > $semVer->getPatch()) {
            if ($this->getMajor() === $semVer->getMajor() && $this->getMinor() === $semVer->getMinor()) {
                return true;
            }
        }
        return false;
    }
    /**
     * method
     * 
     * 
     * @return bool
     */
    public function isLowerThan(ISemVer $semVer) : bool
    {
        if ($this->getMajor() < $semVer->getMajor()) {
            return true;
        }
        if ($this->getMinor() < $semVer->getMinor()) {
            if ($this->getMajor() === $semVer->getMajor()) {
                return true;
            }
        }
        if ($this->getPatch() < $semVer->getPatch()) {
            if ($this->getMajor() === $semVer->getMajor() && $this->getMinor() === $semVer->getMinor()) {
                return true;
            }
        }
        return false;
    }
    /**
     * method
     * 
     * 
     * @return bool
     */
    public function isEqualTo(ISemVer $semVer) : bool
    {
        if ($this->getMajor() !== $semVer->getMajor()) {
            return false;
        }
        if ($this->getMinor() !== $semVer->getMinor()) {
            return false;
        }
        if ($this->getPatch() !== $semVer->getPatch()) {
            return false;
        }
        return true;
    }
}