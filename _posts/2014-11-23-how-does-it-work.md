---
layout: post
title:  "How does it work?"
date:   2014-11-23 15:57:42
categories: howtos
position: 2
---

The plugin handles any package with a type `***-assets`, which will be considered as a
standard library by Composer if you don't use the plugin (and will be installed as any
other classic library package).

### Schema of usage during a project lifecycle

**Installation/Update/Removal by Composer**

The `\AssetsManager\Composer\Installer` installs the "\*\*\*-assets" packages:

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

By default, Composer will install your dependencies in a `vendor/` directory and build an
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

Then, you just have to use the internal PHP handlers designed to let you access, merge or
minify any required asset. See the [Usage page]({{ site.baseurl }}{% post_url 2014-11-23-usage %}) for more information.
