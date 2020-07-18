<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

/**
 *
 * @author Justus
 */
use ArrayAccess;

interface IConfiguration extends ArrayAccess
{
    /**
     * method
     * 
     * 
     * @return self
     */
    
    static function parseJson(string $data) : self;
    
    /**
     * method
     * 
     * 
     * @return mixed
     */
    
    function __construct(array $settings = []);
    
    /**
     * method
     * 
     * 
     * @return mixed
     */
    
    function getSetting(string $name, $default = null);
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    function getSettingAsBool(string $name, bool $default = false) : bool;
    
    /**
     * method
     * 
     * 
     * @return string
     */
    
    function getSettingAsString(string $name, string $default = '') : string;
    
    /**
     * method
     * 
     * 
     * @return int
     */
    
    function getSettingAsInt(string $name, int $default = 0) : int;
    
    /**
     * method
     * 
     * 
     * @return float
     */
    
    function getSettingAsFloat(string $name, float $default = 0.0) : float;
    
    /**
     * method
     * 
     * 
     * @return array
     */
    
    function getSettingAsArray(string $name, array $default = []) : array;
    
    /**
     * method
     * 
     * @return array
     */
    
    function toArray() : array;

}