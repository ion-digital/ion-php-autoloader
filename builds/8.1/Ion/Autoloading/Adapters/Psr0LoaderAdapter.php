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
class Psr0LoaderAdapter extends LoaderAdapter
{
    protected function loadClass(string $className) : ?string
    {
        $path = realpath($this->getIncludePath()) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
        if (file_exists($path)) {
            include $path;
            return $path;
        }
        return null;
    }
}