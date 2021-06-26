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
    public static function parseJson(string $data) : IConfiguration
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
    public final function getSetting(string $name, $default = null)
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
    protected final function setSetting(string $name, $value = null) : IConfiguration
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
    public final function getSettingAsBool(string $name, bool $default = false) : bool
    {
        return boolval($this->getSetting($name, $default));
    }
    /**
     * method
     * 
     * 
     * @return string
     */
    public final function getSettingAsString(string $name, string $default = '') : string
    {
        return (string) $this->getSetting($name, $default);
    }
    /**
     * method
     * 
     * 
     * @return int
     */
    public final function getSettingAsInt(string $name, int $default = 0) : int
    {
        return intval($this->getSetting($name, $default));
    }
    /**
     * method
     * 
     * 
     * @return float
     */
    public final function getSettingAsFloat(string $name, float $default = 0.0) : float
    {
        return floatval($this->getSetting($name, $default));
    }
    /**
     * method
     * 
     * 
     * @return array
     */
    public final function getSettingAsArray(string $name, array $default = []) : array
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
    public final function toArray() : array
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
    public function offsetExists($offset) : bool
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