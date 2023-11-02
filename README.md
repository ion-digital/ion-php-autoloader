# Auto Loader

[![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg)](https://www.gnu.org/licenses/lgpl-3.0)

Auto-Loader is a small, Composer-compatible and customizable auto-loading library for PHP.

### Why not Composer's Auto-loader?

The obvious question... 

Composer's auto-loading capabilities are actually recommended and can be used along-side this library - however, in certain circumstances they can fall a bit short...

This library was created to address those very specific situations. For instance, let's consider a scenario where you would like to seperate your base source code from different source code bases that depend on different versions of PHP.

With composer, you're stuck with one base source code directory - with this library, you can specify multiple.


## Features

* Seperation of source directories and additional build directories.
* Version management (just edit __version.json__ or __composer.json__ with [SemVer](http://semver.org/) compatible version data and you're good to go).
* Class location caches (cached using PHP syntax - to take advantage of the PHP op-cache, if available).
* [PSR-0](https://www.php-fig.org/psr/psr-0/ "php-fig.org") and [PSR-4](https://www.php-fig.org/psr/psr-4/ "php-fig.org").
* Full control (you can turn features like debugging or caching on globally, or just for a single package).


## Getting Started

###As an included library, with Composer:

Make sure Composer is installed - if not, you can get it from [here](https://getcomposer.org/ "getcomposer.org").

First, you need to add _ion/auto-loader_ as a dependency in your _composer.json_ file.

To use the current stable version, add the following to download it straight from [here](https://packagist.org/ "packagist.org"):

```
"require": {
    "php": ">=7.4",
    "ion/auto-loader": "^1.0.0",
}
```

To use the bleeding edge (development) version, add the following:

```
"require": {
    "php": ">=8.2",
    "ion/autoloader": "dev-default",	
},
"repositories": {
    {
      "type": "vcs",
      "url": "https://github.com/ion-digital/ion-php-autoloader.git"
    }
}

Then run the following in the root directory of your project:

> php composer.phar install


Then run the following in the root directory of your project:

> php composer.phar install

### Prerequisites

* Composer (_optional_)


## Built With

* [Composer](https://getcomposer.org/) - Dependency Management

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/ion-digital/ion-php-autoloader/tags "GitHub"). 

## Authors

* **Justus Meyer** - *Initial work* - [GitHub](https://justusmeyer.com/github), [Upwork](https://justusmeyer.com/upwork)

## License

This project is licensed under the LGPL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details.
