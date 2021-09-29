<?php
namespace ion;

use ArrayAccess;
interface ConfigurationInterface extends ArrayAccess
{
    /**
     * method
     * 
     * 
     * @return ConfigurationInterface
     */
    static function parseJson(string $data) : ConfigurationInterface;
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
    function getSettingAsString(string $name, string $default = "") : string;
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
    function getSettingAsFloat(string $name, float $default = 0) : float;
    /**
     * method
     * 
     * 
     * @return array
     */
    function getSettingAsArray(string $name, array $default = [[]]) : array;
    /**
     * method
     * 
     * @return array
     */
    function toArray() : array;
    /**
     * method
     * 
     * 
     * @return bool
     */
    function offsetExists($offset) : bool;
    /**
     * method
     * 
     * 
     * @return mixed
     */
    function offsetGet($offset);
    /**
     * method
     * 
     * 
     * @return void
     */
    function offsetSet($offset, $value) : void;
    /**
     * method
     * 
     * 
     * @return void
     */
    function offsetUnset($offset) : void;
}