# Tempearly
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
