---
layout: page
title: About AssetsManager 
---

A [Composer](http://getcomposer.org/) plugin to manage `***-assets` package type.


## How does it work?

The goal of this [Composer](http://getcomposer.org/) plugin is to **manage the assets files of a package**
(javascript libraries, CSS stylesheets or views) just like Composer manages PHP sources.
Assets files are downloaded and stored in a specific `vendor` directory and an internal
system permits to retrieve and load assets just like PHP classes (a kind of *assets autoloader*).
Some **presets** can be defined in package's configuration to construct a full set of assets files
grouped by type, library or usage.

Just like any standard Composer feature, all names or configuration variables are configurable.


## Development & Documentation

As for all our work, we try to follow the coding standards and naming rules most commonly in use:

-   the [PEAR coding standards](http://pear.php.net/manual/en/standards.php)
-   the [PHP Framework Interoperability Group standards](http://github.com/php-fig/fig-standards).

Knowing that, all classes are named and organized in an architecture to allow the use of the
[standard SplClassLoader](http://gist.github.com/jwage/221634).

The whole package is embedded in the `AssetsManager` namespace.

To install all PHP packages for development, just run:

    ~$ composer install --dev

A development documentation can be generated with [Sami](http://github.com/fabpot/Sami) running:

    ~$ php vendor/sami/sami/sami.php render sami.config.php

The latest version of this development documentation is available online at <http://docs.ateliers-pierrot.fr/assets-manager/>.


## Author & License

*AssetsManager* is authored and maintained by **Pierre Cassat** (aka. [@piwi](http://e-piwi.fr/)) and contributors for
**Les Ateliers Pierrot**. It is an open source software proposed under a [GNU GPL v3.0 license]({{ site.baseurl }}/license.html).

Les Ateliers Pierrot - Paris, France <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
