<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

use Exception;
use \Exception as Throwable;
interface ConfigurationExceptionInterface extends IConfigurationException
{
    /**
     * method
     * 
     * 
     * @return mixed
     */
    function __construct($message = "", $code = 0, Throwable $previous = null);
}