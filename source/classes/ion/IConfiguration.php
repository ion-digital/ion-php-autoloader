<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion;

/**
 *
 * @author Justus
 */

use \ArrayAccess;

interface IConfiguration extends ArrayAccess {
    
    static function parseJson(string $data): self;

    function __construct(array $settings = []);
        
    function getSetting(string $name, $default = null);
    
    function getSettingAsBool(string $name, bool $default = false): bool;
    
    function getSettingAsString(string $name, string $default = ''): string;
    
    function getSettingAsInt(string $name, int $default = 0): int;
    
    function getSettingAsFloat(string $name, float $default = 0.0): float;
    
    function getSettingAsArray(string $name, array $default = []): array;
    
    function toArray(): array;
        
}
