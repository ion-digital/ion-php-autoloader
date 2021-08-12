<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion\Packages;

use Exception;
use \Exception as Throwable;
interface PackageExceptionInterface extends IPackageException
{
    /**
     * method
     * 
     * 
     * @return mixed
     */
    function __construct($message = "", $code = 0, Throwable $previous = null);
}