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
interface SemVerInterface extends ISemVer
{
    /**
     * method
     * 
     * 
     * @return SemVerInterface
     */
    static function create($major = 0, $minor = 0, $patch = 0, $release = null, array $buildData = null);
    /**
     * method
     * 
     * 
     * @return ?SemVerInterface
     */
    static function parse($string);
    /**
     * method
     * 
     * 
     * @return ?SemVerInterface
     */
    static function parsePackageJson($data);
    /**
     * method
     * 
     * 
     * @return ?SemVerInterface
     */
    static function parseComposerJson($data);
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
    function __construct($major = 0, $minor = 0, $patch = 0, $release = null, array $buildData = null);
    /**
     *
     * Get the major version component.
     *
     * @return int Returns the major version component.
     *
     */
    function getMajor();
    /**
     *
     * Get the minor version component.
     *
     * @return int Returns the minor version component.
     *
     */
    function getMinor();
    /**
     *
     * Get the patch version component.
     *
     * @return int Returns the patch version component.
     *
     */
    function getPatch();
    /**
     *
     * Get the release version component.
     *
     * @return int Returns the patch version component.
     *
     */
    function getRelease();
    /**
     *
     * Get the build data version component.
     *
     * @return int Returns the patch version component.
     *
     */
    function getBuildData();
    /**
     *
     * Get the version as a string.
     *
     * @return string Return the version as a string.
     *
     */
    function toString();
    /**
     *
     * Get the version as a VCS tag (e.g: v0.0.0)
     *
     * @return string The version as a VCS tag.
     *
     */
    function toVcsTag();
    /**
     *
     * Get the version as an array.
     *
     * @return array Return the version as an array.
     *
     */
    function toArray();
    /**
     * method
     * 
     * @return string
     */
    function __toString();
    /**
     *
     * Checks to see if this version is higher than the specified version.
     *
     * @param SemVerInterface $semVer The specified version to check.
     *
     * @return bool Returns __true__ if the version is higher, __false__ if not.
     *
     */
    function isHigherThan(SemVerInterface $semVer);
    /**
     *
     * Checks to see if this version is lower than the specified version.
     *
     * @param SemVerInterface $semVer The specified version to check.
     *
     * @return bool Returns __true__ if the version is lower, __false__ if not.
     *
     */
    function isLowerThan(SemVerInterface $semVer);
    /**
     *
     * Checks to see if this version is equal to the specified version.
     *
     * @param SemVerInterface $semVer The specified version to check.
     *
     * @return bool Returns __true__ if the version is equal, __false__ if not.
     *
     */
    function isEqualTo(SemVerInterface $semVer);
}