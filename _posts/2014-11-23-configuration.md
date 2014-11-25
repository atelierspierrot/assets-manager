---
layout: post
title:  "Configuration"
date:   2014-11-23 15:57:42
categories: howtos
author: lesateliers
position: 4
---

Below is an example of the package configuration using default values:

{% highlight json lineanchors linenos startinline %}
"extra": {
    ...

    "assets-dir": "www",
    "assets-vendor-dir": "vendor",
    "document-root": "www",
    "cache-dir": "tmp",
    "assets-config-class": "AssetsManager\\Config\\DefaultConfig",
    "assets-package-class": "AssetsManager\\Package\\Package",
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
{% endhighlight %}

### `assets-dir`: string

This defines the relative path of your assets in the package. This directory must exist
and must be unique (its value must be a string).

### `assets-vendor-dir`: string

This defines the relative path of your packages' assets from the `assets-dir` directory above.
This directory will be created if it doesn't exist and must be unique (its value must be a string).
This value is finally concatenated to the `assets-dir` to build the relative final directory
path of your dependencies assets.

### `document-root`: string (root only)

This defines the relative path used to build the URLs to include your package's assets ; 
this must be the base directory of your HTTP root.
This directory must exist and is unique (its value must be a string). It is only considered
for the root package.

Example:

For instance, if the absolute path of a CSS stylesheet is `/home/www/project/www/assets/package/styles.css`
and your `document-root` is define to `/home/www/project/www/`, the stylesheet tag will be rendered as:

{% highlight html lineanchors linenos startinline %}
<link src="www/assets/package/styles.css" type="text/css" rel="stylesheet" />
{% endhighlight %}

### `cache-dir`: string (root only)

This defines the relative path of the temporary assets files directory.
This directory will be created if it doesn't exist and must be unique (its value must be a string).
This value is finally concatenated to the `assets-dir` to build the relative final directory
path of your assets cache. It is only considered for the root package.

### `assets-presets`: array of arrays

An assets preset is a predefined set of CSS or Javascript files required to use a specific
tool (such as a jQuery plugin for instance). Each preset can be used in a view file writing:

{% highlight php lineanchors linenos startinline %}
echo $assets_loader->getPreset( preset name )->__toHtml();
{% endhighlight %}

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

{% highlight json lineanchors linenos startinline %}
"jsfiles_footer": [
    "vendor/jquery.metadata.js",
    "min:vendor/jquery.tablesorter.min.js"
]
{% endhighlight %}

This way, the library can separate already minified files from others.

You can also define **a position** for your asset file in the global assets files stack. This
can be useful for instance if your preset defines two jQuery plugins, A & B, and if B requires
A to work. In this case, you may define a higher position for A than for B to be sure that
the A files will be loaded before the B ones.

Position is an integer in range `[ -1 ; 100 ]` where `100` is the top file of the stack 
and `-1` the last one. You can simply write `first` or `last` if you don't really mind, which
are respectively considered as `100` and `-1`.

Example:

{% highlight json lineanchors linenos startinline %}
"jsfiles_footer": [
    "first:vendor/jquery.metadata.js",
    "last:min:vendor/jquery.tablesorter.min.js"
]
// or
"jsfiles_footer": [
    "10:vendor/jquery.metadata.js",
    "11:min:vendor/jquery.tablesorter.min.js"
]
{% endhighlight %}

### PHP classes (root only)

You can also overwrite the default classes used to manage the configuration, the package and
preset and the installation of the assets.

#### `assets-config-class`: string

This defines the class used as the default configuration values of your root package. The
class must exist and implement the `\AssetsManager\Config\ConfiguratorInterface` interface.

It defaults to `\AssetsManager\Config\DefaultConfig`.

#### `assets-package-class`: string

This defines the class used to handle each assets package during installation and assets
loading. The class must exist and implement the `\AssetsManager\Package\PackageInterface`
interface.

It defaults to `\AssetsManager\Package\Package`.

#### `assets-preset-class`: string

This defines the class used to handle each assets preset. The class must exist and implement
the `\AssetsManager\Package\PresetInterface` interface.

It defaults to `\AssetsManager\Package\Preset`.

#### `assets-package-installer-class`: string

This defines the class used for packages installation by Composer. The class must exist and
implement the `\AssetsManager\Composer\Installer\AssetsInstallerInterface` interface.

It defaults to `\AssetsManager\Composer\Installer\AssetsInstaller`.

#### `assets-autoload-generator-class`: string

This defines the class used for the assets database JSON file generator. The class must exist
and extend the abstract class `\AssetsManager\Composer\Autoload\AbstractAssetsAutoloadGenerator`.

It defaults to `\AssetsManager\Composer\Autoload\AssetsAutoloadGenerator`.

