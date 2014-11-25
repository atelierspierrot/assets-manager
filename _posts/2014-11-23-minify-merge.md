---
layout: post
title:  "Minify & merge assets"
date:   2014-11-23 15:57:42
categories: howtos
author: lesateliers
position: 5
---

The plugin embeds a `Compressor` object to *merge* and *minify* JS and CSS contents. The
result files are stored on a the "temporary assets directory" (a configuration variable)
and can be used "as-is" in HTML contents.

Please see the [note below](#note) for an information (and a warning) about the minification
processes implemented by the plugin.

## Merging assets

Creation of an `\AssetsManager\Compressor\Compressor` object:

{% highlight php lineanchors linenos startinline %}
$compressor = new AssetsManager\Compressor\Compressor(
    // the files to treat
    $js_stack,
    // the result filename - null will cause automatic generation
    null,
    // the result file path
    __DIR__."/../tmp",
    // the file type - will be guessed if null
    "js"
);
$compressor->process();
{% endhighlight %}

We can ask the compressor in which file it has generate its merge:

{% highlight php lineanchors linenos startinline %}
$minified_filename = $compressor->getDestinationRealPath();
// => /absolute/path/to/tmp/d1811861a0e25d97d8285e32cade0ff6_merge.js
{% endhighlight %}

We can get a ready-to-use file path of the generated file defining the web root:

{% highlight php lineanchors linenos startinline %}
$compressor->setWebRootPath( $loader->getDocumentRoot() );
$minified_webpath = $compressor->getDestinationWebPath();
// => tmp/d1811861a0e25d97d8285e32cade0ff6_merge.js 
{% endhighlight %}

## Minifying assets

Ask the minifier to process our files stack:

{% highlight php lineanchors linenos startinline %}
$compressor
    ->setAdapterAction("minify")
    ->process();
{% endhighlight %}

Ask the compressor in which file it has generate minify our files:

{% highlight php lineanchors linenos startinline %}
$minified_filename = $compressor->getDestinationRealPath();
// => /absolute/path/to/tmp/d1811861a0e25d97d8285e32cade0ff6_minify.js
{% endhighlight %}

We can get a ready-to-use file path of the generated file defining the web root:

{% highlight php lineanchors linenos startinline %}
$compressor->setWebRootPath( $loader->getDocumentRoot() );
$minified_webpath = $compressor->getDestinationWebPath();
// => tmp/d1811861a0e25d97d8285e32cade0ff6_minify.js 
{% endhighlight %}

## Note

<a id="note"></a>
Please be aware that the minification process defined in the plugin is "simple" and "custom".
It does NOT use any external tool to optimize assets contents. For a better rendering, you
MAY use an external tool.
