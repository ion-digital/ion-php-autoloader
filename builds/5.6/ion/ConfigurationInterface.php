<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

use ArrayAccess;
interface ConfigurationInterface extends IConfiguration
{
    /**
     * method
     * 
     * 
     * @return ConfigurationInterface
     */
    static function parseJson($data);
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
    function getSetting($name, $default = null);
    /**
     * method
     * 
     * 
     * @return bool
     */
    function getSettingAsBool($name, $default = false);
    /**
     * method
     * 
     * 
     * @return string
     */
    function getSettingAsString($name, $default = "");
    /**
     * method
     * 
     * 
     * @return int
     */
    function getSettingAsInt($name, $default = 0);
    /**
     * method
     * 
     * 
     * @return float
     */
    function getSettingAsFloat($name, $default = 0);
    /**
     * method
     * 
     * 
     * @return array
     */
    function getSettingAsArray($name, array $default = []);
    /**
     * method
     * 
     * @return array
     */
    function toArray();
    /**
     * method
     * 
     * 
     * @return bool
     */
    function offsetExists($offset);
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
    function offsetSet($offset, $value);
    /**
     * method
     * 
     * 
     * @return void
     */
    function offsetUnset($offset);
}