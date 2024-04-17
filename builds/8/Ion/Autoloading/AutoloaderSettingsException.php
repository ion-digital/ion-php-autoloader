<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace Ion\Autoloading;

/**
 * Description of AutoloaderException
 *
 * @author Justus
 */

use \Exception;
use \Throwable;

class AutoloaderSettingsException extends AutoloaderException implements AutoloaderSettingsExceptionInterface {
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        
        parent::__construct($message, $code, $previous);
    } 
}
