<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\AutoLoading;

/**
 * Description of AutoLoaderException
 *
 * @author Justus
 */

use \Exception;
use \Throwable;

class AutoLoaderException extends Exception implements AutoLoaderExceptionInterface {
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        
        parent::__construct($message, $code, $previous);
    }
    
}
