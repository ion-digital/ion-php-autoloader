<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace Ion\Autoloading\Adapters;

/**
 * Description of PsrLoader
 *
 * @author Justus
 */
use Ion\Autoloading\LoaderAdapter;
class Psr4LoaderAdapter extends Psr0LoaderAdapter
{
    protected function loadClass(string $className) : ?string
    {
        $path = realpath($this->getIncludePath()) . DIRECTORY_SEPARATOR . substr($className, strrpos($className, DIRECTORY_SEPARATOR)) . '.php';
        if (file_exists($path)) {
            include $path;
            return $path;
        }
        return parent::loadClass($className);
    }
}