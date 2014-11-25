---
layout: page
title: About AssetsManager 
---

A [Composer](http://getcomposer.org/) plugin to manage `***-assets` package type.


## What is this?

The goal of the *Assets Manager* [Composer](http://getcomposer.org/)'s plugin is to **manage the assets files of a package** 
(javascript libraries, CSS stylesheets or views) just like it natively does for PHP sources. 
Assets files are downloaded and stored in a specific `vendor` directory and an internal 
system permits to retrieve and load some assets just like PHP classes (a kind of *assets autoloader*). 
Some presets can be defined in package's configuration to construct a full set of assets files grouped 
by type, library or usage.

Just like any standard Composer feature, all names or paths are configurable.


## Author & License

*AssetsManager* is authored and maintained by **Pierre Cassat** (aka. [@piwi](http://e-piwi.fr/)) and contributors for
**Les Ateliers Pierrot**. It is an open source software proposed under a [GNU GPL v3.0 license]({{ site.baseurl }}/license.html).

Contents of the [assetsmanager.ateliers-pierrot.fr](http://assetsmanager.ateliers-pierrot.fr/) website are 
licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) 
license.

Les Ateliers Pierrot - Paris, France <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>


## Manifest of last release

The list below presents the `composer.json` information of last stable release you can
find at <https://github.com/atelierspierrot/assets-manager/releases>. For more information
about the Composer's manifest schema, see <https://getcomposer.org/doc/04-schema.md>.

```
{% include composer.json %}
```

## Package links

-   The sources of the package are hosted on [GitHub](http://github.com/) and available
    at <http://github.com/atelierspierrot/assets-manager>.
-   The last stable release of the package is the last archive you may find at 
    <http://github.com/atelierspierrot/assets-manager/releases>.
-   To transmit a bug or make a comment, use the ticketing manager available at 
    <http://github.com/atelierspierrot/assets-manager/issues>.
-   As the sources are public and open source, if you want to contribute, you can 
    [fork and edit the original repository](http://help.github.com/articles/fork-a-repo) 
    to finally make a [pull request](http://help.github.com/articles/using-pull-requests)
    to the integrate your work in original sources.
-   The sources documentation of last release can be found at
    <http://assetsmanager.ateliers-pierrot.fr/phpdoc/>.
