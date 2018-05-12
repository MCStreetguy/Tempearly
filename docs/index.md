title: Tempearly
description: A tiny PHP templating engine.

<h1 id="tempearly">Tempearly</h1>

[![Packagist](https://img.shields.io/packagist/v/mcstreetguy/tempearly.svg)](https://packagist.org/packages/mcstreetguy/tempearly)
[![Packagist](https://img.shields.io/packagist/dt/mcstreetguy/tempearly.svg)](https://packagist.org/packages/mcstreetguy/tempearly)
[![GitHub tag](https://img.shields.io/github/tag/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly)
[![Packagist](https://img.shields.io/packagist/l/mcstreetguy/tempearly.svg)](https://packagist.org/packages/mcstreetguy/tempearly)
[![GitHub issues](https://img.shields.io/github/issues/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/issues)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/pulls)
[![GitHub last commit](https://img.shields.io/github/last-commit/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/commits/master)
[![GitHub repo size in bytes](https://img.shields.io/github/repo-size/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly)
[![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/tree/master/src)

**A tiny PHP templating engine.**   
Tempearly uses Regular Expressions to provide fast but still powerful features to your templates.

## Installation

### Composer

Require the module through Composer:    

``` bash
$ composer require mcstreetguy/tempearly
```

Tempearly registers itself and it's components in the PSR-4 namespace.
Ensure you include Composer's autoloader:

``` php
include_once 'vendor/autoload.php';
```

### Manually

If you don't use Composer you can also download a source archive from GitHub and include the files manually.

> https://github.com/MCStreetguy/Tempearly/archive/master.zip

_<small>(Replace 'master' with your desired version)</small>_

## Usage
First initiate a Tempearly instance by providing the path to your template folder
and optionally the file extension of your templates. The extension defaults to `.tpl.html`
but the path has to be provided.

```PHP
$engine = new MCStreetguy\Tempearly('my/template/folder','.html');
```

Now you can invoke the rendering process of any template within your template folder as following:

```PHP
$engine->render('my-template');
```

You may structure your templates in subdirectories. Then provide the relative path as template id:

```PHP
$engine->render('login/view/registration');
```

The `render(...)` function returns the parsed template as string. Thus you may echo it
directly to your page, or work on it further as you wish.

### User Context
You can provide an optional array of context-variables to the rendering process as seconds argument:

```PHP
$engine->render('my-template',array(
  'foo' => 'Hello World!',
  'bar' => true,
  'baz' => 27
));
```

You can also directly pass a Context instance. The use of that class is especially
handy if you need to fill it dynamically instead of just passing static values as array.

```PHP
$context = new MCStreetguy\Tempearly\Context();

$context->push('foo','Hello World!');
$context->push('bar',true);
$context->push('baz',27);

$engine->render('my-template',$context);
```

You may also pass an array to the Context constructor, making it use that array
as context contents. Modifications are still possible afterwards.

```PHP
$context = new MCStreetguy\Tempearly\Context(array(
  'foo' => 'Hello World!',
  'bar' => true
));

$context->push('baz',27);

$engine->render('my-template',$context);
```

There is no restriction to the context values, the parameter is defined as `mixed`.
Nevertheless I recommend only using simple types and callables.

### Minification
Tempearly provides a static minify function that removes all unnecessary whitespaces from an HTML string.
Whitespaces are considered unnecessary if they occur multiple times or outside of HTML tags.

```PHP
Tempearly::minify($myTemplate);
```
