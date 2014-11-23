---
layout: post
title:  "Installation"
date:   2014-11-23 15:57:42
categories: howtos
position: 1
---

*AssetsManager* is referenced to the [Packagist](https://packagist.org/packages/atelierspierrot/assets-manager).
To install the plugin, just add the package to your requirements in the `composer.json` configuration
file of your project:

{% highlight json lineanchors linenos startinline %}
"require": {
    ...
    "atelierspierrot/assets-manager": "1.*"
}
{% endhighlight %}

The last stable release is the last tag you can find at <https://github.com/atelierspierrot/assets-manager/releases>.

As we try to let the `master` branch in a stable status (only tested features are merged in it), you
can use (quite safely) a version number like `"dev-master"` to use the last "master" branch
of the plugin.

For information about the configurations expected by the plugin, see [the Usage page]({{ site.baseurl }}{% post_url 2014-11-23-usage %}).

## Development version

If you need to work *on* or *with* the plugin sources, you can also [download an archive](https://github.com/atelierspierrot/assets-manager/archive/master.zip)
of current `master` branch and use it "as-is". If you do so, keep in mind that the plugin
remains on three dependencies you MAY install for it to work:

-   our [PHP Patterns](https://github.com/atelierspierrot/patterns) package: `atelierspierrot/patterns`
-   our [PHP Library](https://github.com/atelierspierrot/library) package: `atelierspierrot/library`
-   the [Composer's plugin API](http://getcomposer.org/) package: `composer-plugin-api`
