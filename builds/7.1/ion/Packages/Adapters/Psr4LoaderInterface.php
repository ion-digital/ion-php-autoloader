<?php
namespace ion\Packages\Adapters;

use ion\Packages\Adapters\Psr0LoaderInterface;
use ion\Packages\Adapters\IPsr4Loader;
interface Psr4LoaderInterface extends Psr0LoaderInterface, IPsr4Loader
{
}