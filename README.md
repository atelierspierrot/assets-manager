Assets Manager
==============

A [Composer](http://getcomposer.org/) plugin to manage `***-assets` package type.


## How does it work?

The goal of this [Composer](http://getcomposer.org/) plugin is to **manage the assets files of a package**
(javascript libraries, CSS stylesheets or views) just like Composer manages PHP sources.
Assets files are downloaded and stored in a specific `vendor` directory and an internal
system permits to retrieve and load assets just like PHP classes (a kind of *assets autoloader*).
Some **presets** can be defined in package's configuration to construct a full set of assets files
grouped by type, library or usage.

Just like any standard Composer feature, all names or configuration variables are configurable.

## Installation

To install the plugin, just add the package to your requirements in your `composer.json`:

    "require": {
        ...
        "atelierspierrot/assets-manager": "1.*"
    }

The namespace will be automatically added to the project's Composer *autoloader* and once it will
be installed, the plugin will handle assets packages installation.


## Usage

### Schema of usage during a project lifecycle

**Installation/Update by Composer**

The `\AssetsManager\Composer\Installer` installs the "library-assets" packages:

- it moves the assets in a specific `www/vendor/` directory
- it adds an entry to the internal `assets_db` table
- finally, it writes the internal `assets_db` table in file `vendor/assets.json`

**Life of the project**

The `\AssetsManager\Loader` reads the internal `assets_db` and manages assets packages and presets:

- it reads the `assets.json` db file
- it finds some assets packages files (realpath and web URL)
- it manages the assets presets


### Structure

Let's say your project is based on the following structure, where `src/` contains
your PHP sources and `www/` is your web document root:

    | composer.json
    | src/
    | www/

By default, Composer will install your dependencies in a `vendor/` directory and build a
`autoload.php` file:

    | composer.json
    | src/
    | vendor/
    | ------- autoload.php
    | www/

The plugin will copy the assets of your dependencies in a `www/vendor/` directory and
build a JSON map in the original `vendor/`:

    | composer.json
    | src/
    | vendor/
    | ------- autoload.php
    | ------- assets.json
    | www/
    | ---- vendor/

### How to inform the extension about your package assets?

The extension handles any package of type `***-assets`, which will be considered as a
standard library by Composer if you don't use the extension (and will be installed as any
other classic library package).


## Usage of the assets manager

### The assets loader object

The `\AssetsManager\Loader` class is designed to manage a set of assets based on a table of 
installed packages.

The class is based on three paths:

- `base_dir`: the package root directory, which must be the directory containing the `composer.json` file
- `assets_dir`: the package asssets directory related to `base_dir`
- `document_root`: the path in the filesystem of the web assets root directory ; this is used
to build all related assets HTTP URLs.

For these three paths, their defaults values are defined on a default package structure:

    package_name/
    |----------- src/
    |----------- www/

    $loader->base_dir = realpath(package_name)
    $loader->assets_dir = www
    $loader->document_root = www or the server DOCUMENT_ROOT

NOTE - These paths are stored in the object without the trailing slash.

### Usage example

Once the install process is done, you can access any assets package or load a package's preset
using the `\AssetsManager\Loader` object:

    $loader = \AssetsManager\Loader::getInstance(
        __DIR__.'/..',      // this is the project root directory
        'www',              // this is your assets root directory
        __DIR__             // this is your web document root
    );

    // to get a package
    $package = $loader->getPackage( package name );

    // to get a preset
    $preset = $loader->getPreset( preset name );
    // to write preset dependencies
    echo $preset->__toHtml();

As described in the "configuration" section below, calling a preset will automatically load
its internal files requirements (some Javascript files for instance) and its dependencies to
other presets or files. The result of the `__toHtml()` method will then be a string to include
the files and scripts definitions, fully functional and ready to be written in your HTML.

Example:

For instance, if you installed the [Gentleface sprites](http://github.com/atelierspierrot/gentleface-sprites)
package in its `assets-installer` version you will have:

    // calling ...
    echo $loader->getPreset('gentleface-sprites')->__toHtml();
    // will render something like:
    <link src="vendor/atelierspierrot/gentleface-sprites/gentleface-sprites.min.css" type="text/css" rel="stylesheet" media="all" />


## Configuration

Below is the example of the package default configuration values:

    "extra": {
        "assets-dir": "www",
        "assets-vendor-dir": "vendor",
        "document-root": "www",
        "assets-config-class": "AssetsManager\\Config\\DefaultConfig",
        "assets-package-class": "AssetsManager\\Package\\AssetsPackage",
        "assets-preset-class": "AssetsManager\\Package\\Preset",
        "assets-package-installer-class": "AssetsManager\\Composer\\Installer\\AssetsInstaller",
        "assets-autoload-generator-class": "AssetsManager\\Composer\\Autoload\\AssetsAutoloadGenerator",

        // this part is just for the example, no asset is embedded with the package
        "assets-presets": {
            "jquery.tablesorter": {
                "css": "vendor_assets/blue/style.css",
                "jsfiles_footer": [
                    "vendor_assets/jquery.metadata.js",
                    "min:vendor/jquery.tablesorter.min.js"
                ]
            },
            "jquery.highlight": {
                "css": "vendor_assets/jquery.highlight.css",
                "jsfiles_footer": "vendor_assets/jquery.highlight.js"
            }
        }
    }

All the paths are relative to the package `vendor` installation directory or the `assets`
installation directory.

### `assets-dir`: string

This defines the relative path of your assets in the package. This directory must exist
and must be unique (its value must be a string).

### `assets-vendor-dir`: string

This defines the relative path of your packages' assets from the `assets-dir` directory above.
This directory will be created if it doesn't exist and must be unique (its value must be a string).
This value is finally concatenated to the `assets-dir` to build the relative final directory
path of your dependencies assets.

Example:

For instance, if your `assets-dir` is set to "www" and your `assets-vendor-dir` is set to
"assets_vendor", the final place of your dependencies assets will be "www/assets_vendor/"
related to your project root directory.

### `document-root`: string (root only)

This defines the relative path used to build the URLs to include your package's assets ; 
this must be the base directory of your HTTP root.
This directory must exist and is unique (its value must be a string). It is only considered
for the root package.

Example:

For instance, if the absolute path of a CSS stylesheet is `/home/www/project/www/assets/package/styles.css`
and your `document-root` is define to `/home/www/project/www/`, the stylesheet tag will be rendered as:

    <link src="www/assets/package/styles.css" type="text/css" rel="stylesheet" />

### `assets-presets`: array of arrays

An assets preset is a predefined set of CSS or Javascript files to include to use a specific
tool (such as a jQuery plugin for instance). Each preset can be used in a view file writing:

        $preset = $assets_loader->getPreset( preset name );
        echo $preset->load()->__toHtml();

A preset is defined as a set of `key => array` pairs where the `key` is the preset name 
(the name you will call using the `getPreset()` method) and the corresponding array
defines the required assets files to be included in the whole template. This array can
have indexes in "css", "js", "jsfiles_header", "jsfiles_footer" and "require".

#### `css`: string|array

The CSS entry of a preset is a list of one or more CSS files to include. This must be a list
of existing files and file paths must be relative to the package `assets` directory. Each entry
can also be a valid URL, with or without HTTP protocol.

#### `js`, `jsfiles_header` and `jsfiles_footer`: string|array

These Javascript entries defines respectively some scripts to be included in the page header
or footer. This must be a list of existing files and file paths must be relative to the
package `assets` directory. Each entry can also be a valid URL, with or without HTTP protocol.

#### `require`: string|array

If your preset requires another one, use this entry to define one or more of the required
other presets. These presets must exist in your package or its dependencies.

#### Specific rules

You may inform if one of your preset files is already **minified** or **packed**. To do so, you can
prefix the file path with `min:` or `pack:`.

Example:

    "jsfiles_footer": [
        "vendor/jquery.metadata.js",
        "min:vendor/jquery.tablesorter.min.js"
    ]

This way, the library can separate already minified files from others.

You can also define **a position** for your asset file in the global assets files stack. This
can be useful for instance if your preset defines two jQuery plugins, A & B, and if B requires
A to work. In this case, you may define a higher position for A than for B to be sure that
the A files will be loaded before the B ones.

Position is an integer in range `[ -1 ; 100 ]` where `100` is the top file of the stack 
and `-1` the last one. You can simply write `first` or `last` if you don't really mind, which
are respectively considered as `100` and `-1`.

Example:

    "jsfiles_footer": [
        "first:vendor/jquery.metadata.js",
        "last:min:vendor/jquery.tablesorter.min.js"
    ]

### PHP classes (root only)

#### `assets-config-class`: string

This defines the class used as the default configuration values of your root package. The
class must exist and implement the `\AssetsManager\Config\ConfiguratorInterface` interface.

It defaults to `\AssetsManager\Config\DefaultConfig`.

#### `assets-package-class`: string

This defines the class used to handle each assets package during installation and assets
loading. The class must exist and implement the `\AssetsManager\Package\AssetsPackageInterface`
interface.

It defaults to `\AssetsManager\Package\AssetsPackage`.

#### `assets-preset-class`: string

This defines the class used to handle each assets preset. The class must exist and implement
the `\AssetsManager\Package\AssetsPresetInterface` interface.

It defaults to `\AssetsManager\Package\Preset`.

#### `assets-package-installer-class`: string

This defines the class used for packages installation by Composer. The class must exist and
implement the `\AssetsManager\Composer\Installer\AssetsInstallerInterface` interface.

It defaults to `\AssetsManager\Composer\Installer\AssetsInstaller`.

#### `assets-autoload-generator-class`: string

This defines the class used for the assets database JSON file generator. The class must exist
and extend the abstract class `\AssetsManager\Composer\Autoload\AbstractAssetsAutoloadGenerator`.

It defaults to `\AssetsManager\Composer\Autoload\AssetsAutoloadGenerator`.


## Development

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

>    Assets Manager

>    http://github.com/atelierspierrot/assets-manager

>    Copyleft 2013-2014, Pierre Cassat and contributors

>    Licensed under the GPL Version 3 license.

>    http://opensource.org/licenses/GPL-3.0

>    ----

>    Les Ateliers Pierrot - Paris, France

>    <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
