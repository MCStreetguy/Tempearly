# Tempearly
[![Packagist](https://img.shields.io/packagist/v/mcstreetguy/tempearly.svg)](https://packagist.org/packages/mcstreetguy/tempearly)
[![Packagist](https://img.shields.io/packagist/dt/mcstreetguy/tempearly.svg)](https://packagist.org/packages/mcstreetguy/tempearly)
[![GitHub tag](https://img.shields.io/github/tag/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly)
[![Packagist](https://img.shields.io/packagist/l/mcstreetguy/tempearly.svg)](https://packagist.org/packages/mcstreetguy/tempearly)
[![GitHub issues](https://img.shields.io/github/issues/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/issues)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/pulls)
[![GitHub last commit](https://img.shields.io/github/last-commit/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/commits/master)
[![GitHub repo size in bytes](https://img.shields.io/github/repo-size/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly)
[![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/mcstreetguy/tempearly.svg)](https://github.com/MCStreetguy/Tempearly/tree/master/src)
[![Documentation Status](https://readthedocs.org/projects/tempearly/badge/?version=latest)](http://tempearly.readthedocs.io/en/latest/?badge=latest)

A tiny PHP templating engine.

Visit the [full documentation](https://docs.mcstreetguy.de/Tempearly/) for a detailed explanation.

### Installation
```
$ composer require mcstreetguy/tempearly
```

### Usage
```PHP
$engine = new MCStreetguy\Tempearly('path/to/templates','.tpl.html');
```

---

```PHP
$engine->parse('login-template-1',array(
  'my-variable' => 'Hello World!',
  'another-variable' => true
));
```
