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

use \ArrayAccess;

class Configuration implements ConfigurationInterface, ArrayAccess {
    
    public static function parseJson(string $data): ConfigurationInterface {
        
        $json = json_decode($data, true);
        
        if(is_array($json)) {
            
            return new static($json);
        }
        
        throw new ConfigurationException("Invalid configuration file - could not parse JSON data ('{$data}').");
    }
    
    private $settings = [];
    
    final public function __construct(array $settings = []) {
        
        $this->settings = $settings;        
    }
    
    final public function getSetting(string $name, $default = null) {
        
        if(!array_key_exists($name, $this->settings)) {
            
            return $default;
        }
        
        return $this->settings[$name];
    }
    
    final protected function setSetting(string $name, $value = null): ConfigurationInterface {

        $this->settings[$name] = $value;
        return $this;
    }    
    
    final public function getSettingAsBool(string $name, bool $default = false): bool {
        
        return boolval($this->getSetting($name, $default));
    }
    
    final public function getSettingAsString(string $name, string $default = ''): string {
        
        return (string) ($this->getSetting($name, $default));
    }    
    
    final public function getSettingAsInt(string $name, int $default = 0): int {
        
        return intval($this->getSetting($name, $default));
    }    
    
    final public function getSettingAsFloat(string $name, float $default = 0.0): float {
        
        return floatval($this->getSetting($name, $default));
    }        
    
    final public function getSettingAsArray(string $name, array $default = []): array {
        
        $value = $this->getSetting($name, null);
        
        if($value === null) {
            
            return $default;
        }
        
        if(is_array($value)) {
            
            return $value;
        }
        
        return [ $value ];
    }
    
    final public function toArray(): array {
        
        return $this->settings;
    }
    
    private static function offsetToKey($offset, array $settings): ?string {
        
        if(is_string($offset)) {
            
            return $offset;
        }
            
        $keys = array_keys($settings);

        if(count($keys) > intval($offset)) {

            return $keys[$offset];
        }                        
               
        return null;
    }
    
    public function offsetExists($offset): bool {
    
        $key = static::offsetToKey($offset, $this->settings);
        
        if($key === null) {
            
            return false;
        }
        
        return array_key_exists($key, $this->settings);
    }
    
    public function offsetGet($offset): mixed {
        
        $key = static::offsetToKey($offset, $this->settings);
        
        if($key === null) {
            
            return null;
        }
        
        return $this->settings[$key];
    }
    
    public function offsetSet($offset, $value): void {
  
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
    
    public function offsetUnset($offset): void {
        
        $key = static::offsetToKey($offset, $this->settings);
        
        if($key === null) {
                     
            return;
        }

        $this->settings[$key] = null;
        
        return;
    }
}
