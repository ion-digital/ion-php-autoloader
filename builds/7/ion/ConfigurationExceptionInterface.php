<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

use Exception;
use Throwable;



interface ConfigurationExceptionInterface extends IConfigurationException {


    function __construct(string $message = "", int $code = 0, Throwable $previous = null);
}
