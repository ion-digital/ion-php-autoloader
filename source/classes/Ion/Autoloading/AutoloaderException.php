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

class AutoloaderException extends Exception implements AutoloaderExceptionInterface {
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        
        parent::__construct($message, $code, $previous);
    }
    
}
