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
    function getSettingAsString($name, $default = '');
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
    function getSettingAsFloat($name, $default = 0.0);
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
}