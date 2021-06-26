<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

/**
 * Description of PackageSettings
 *
 * @author Justus
 */
class Configuration implements IConfiguration
{
    /**
     * method
     * 
     * 
     * @return IConfiguration
     */
    public static function parseJson($data)
    {
        $json = json_decode($data, true);
        if (is_array($json)) {
            return new static($json);
        }
        throw new ConfigurationException("Invalid configuration file - could not parse JSON data ('{$data}').");
    }
    private $settings = [];
    /**
     * method
     * 
     * 
     * @return mixed
     */
    public final function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }
    /**
     * method
     * 
     * 
     * @return mixed
     */
    public final function getSetting($name, $default = null)
    {
        if (!array_key_exists($name, $this->settings)) {
            return $default;
        }
        return $this->settings[$name];
    }
    /**
     * method
     * 
     * 
     * @return IConfiguration
     */
    protected final function setSetting($name, $value = null)
    {
        $this->settings[$name] = $value;
        return $this;
    }
    /**
     * method
     * 
     * 
     * @return bool
     */
    public final function getSettingAsBool($name, $default = false)
    {
        return boolval($this->getSetting($name, $default));
    }
    /**
     * method
     * 
     * 
     * @return string
     */
    public final function getSettingAsString($name, $default = '')
    {
        return (string) $this->getSetting($name, $default);
    }
    /**
     * method
     * 
     * 
     * @return int
     */
    public final function getSettingAsInt($name, $default = 0)
    {
        return intval($this->getSetting($name, $default));
    }
    /**
     * method
     * 
     * 
     * @return float
     */
    public final function getSettingAsFloat($name, $default = 0.0)
    {
        return floatval($this->getSetting($name, $default));
    }
    /**
     * method
     * 
     * 
     * @return array
     */
    public final function getSettingAsArray($name, array $default = [])
    {
        $value = $this->getSetting($name, null);
        if ($value === null) {
            return $default;
        }
        if (is_array($value)) {
            return $value;
        }
        return [$value];
    }
    /**
     * method
     * 
     * @return array
     */
    public final function toArray()
    {
        return $this->settings;
    }
    /**
     * method
     * 
     * 
     * @return ?string
     */
    private static function offsetToKey($offset)
    {
        if (is_string($offset)) {
            return $offset;
        }
        $keys = array_keys($this->settings);
        if (count($keys) > intval($offset)) {
            return $keys[$offset];
        }
        return null;
    }
    /**
     * method
     * 
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        $key = static::offsetToKey($offset);
        if ($key === null) {
            return false;
        }
        return array_key_exists($key, $this->settings);
    }
    /**
     * method
     * 
     * 
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $key = static::offsetToKey($offset);
        if ($key === null) {
            return null;
        }
        return $this->settings[$key];
    }
    /**
     * method
     * 
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new ConfigurationException("Configuration settings cannot be changed once loaded.");
        //        $key = static::offsetToKey($offset);
        //
        //        if($key === null) {
        //
        //            return;
        //        }
        //
        //        $this->settings[$key] = $value;
        //
        //        return;
    }
    /**
     * method
     * 
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        $key = static::offsetToKey($offset);
        if ($key === null) {
            return;
        }
        $this->settings[$key] = null;
        return;
    }
}