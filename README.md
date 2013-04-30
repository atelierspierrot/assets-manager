Composer Assets Extension
=========================

This document describes the [Composer](https://getcomposer.org/) extension to manage assets
used by our [Template Engine](https://github.com/atelierspierrot/templatengine) package.

The plugin can be used separately as a stand-alone Composer extension.


## How it works?

The goal of this extension is to manage some packages of assets (*javascript libraries, CSS
frameworks or views*) just like Composer standardly manages PHP packages. Assets packages
are downloaded and stored in a specific `vendor` directory and an internal system allows
you to retrieve and load the assets packages files just as you do with PHP classes (*a kind
of assets autoloader*).

Just like any standard Composer feature, all names or configuration variables are configurable.

## Assets `vendor`

Let's say your project is constructed on the following structure, where `src/` contains
your PHP sources and `www/` is your web document root:

    | composer.json
    | src/
    | www/

By default, Composer will install your dependencies in a `vendor/` directory and build a
PHP autoloader:

    | composer.json
    | src/
    | vendor/
    | ------- autoload.php
    | www/

The Composer extension will copy the assets of your dependencies in a `www/vendor/` directory
and build a JSON map in the original `vendor/`:

    | composer.json
    | src/
    | vendor/
    | ------- autoload.php
    | ------- assets.json
    | www/
    | ---- vendor/

### How to inform the extension about your package assets

The extension will consider any package with an `extra` setting called `assets` as 


## Configuration

Below is the example of the package itself:

    "extra": {
        "assets-dir": "www",
        "assets-vendor-dir": "vendor",
        "document-root": "www",
        "assets-cache-dir": "tmp",
        "assets-presets": {
            "jquery.tablesorter": {
                "css": "vendor_assets/blue/style.css",
                "jsfiles_footer": [ "vendor_assets/jquery.metadata.js", "min:vendor/jquery.tablesorter.min.js" ]
            },
            "jquery.highlight": {
                "css": "vendor_assets/jquery.highlight.css",
                "jsfiles_footer": "vendor_assets/jquery.highlight.js"
            }
        },
    }

## Configuration entries

All the paths are relative to the package `vendor` installation directory or its `assets`
installation directory.

### `assets-dir`: string

This defines the relative path of your assets in the package. This directory must exist
and must be unique (*its value must be a string*).

### `assets-vendor-dir`: string

This defines the relative path of your packages'assets in the `assets` directory above.
This directory will be created if it doesn't exist and must be unique (*its value must be a string*).

### `document-root`: string - only for **root** package

This defines the relative path used to build the URLs to include your package's assets ; 
this must be the base directory of your HTTP root.
This directory must exist and is unique (*its value must be a string*). It is only considered
for the root package.

## `assets-presets`: array of arrays

An assets preset is a predefined set of CSS or Javascript files to include to use a specific
tool (*such as a jQuery plugin for instance*). Each preset can be used in a view file writing:

    _use( preset name );

A preset is defined as a `key => array` pair where the `key` is the preset name (*the name
you will call using the `_use()` method*) and the corresponding array defines the required
assets files to be included in the whole template.

### `css`: string|array

The CSS entry of a preset is a list of one or more CSS files to include. This must be a list
of existing files and file paths must be relative to the package `assets` directory.

### `jsfiles_header` and `jsfiles_footer`: string|array

These Javascript entries defines respectively some scripts to be included in the page header
or footer. This must be a list of existing files and file paths must be relative to the
package `assets` directory.

### Specific rules

As the template engine embeds a `Minifier` for assets, you may inform it if one of your
preset files is already minified or packed. To do so, you can prefix the file path with
`min:` or `pack:`. For instance:

    "jsfiles_footer": [ "vendor/jquery.metadata.js", "min:vendor/jquery.tablesorter.min.js" ]

This way, your file will not be minified if you use this feature.

==========================================================================================
MAP

installation : AssetsInstaller for "library-assets" packages
    => move the assets in `www/vendor/`
    => add an entry to the `assets_db`
    
autoload-dump : AssetsInstaller::autoloadDump to generate assets.json
    => write the `assets_db` in `vendor/assets.json`

life of the project : AssetsAutoloader to read assets.json and manage assets packages and presets
    => read the `assets.json`
    => find some assets packages files (realpath and web URL)
    => manage the assets presets
