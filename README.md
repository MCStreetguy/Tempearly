# Tempearly
A tiny PHP templating engine.

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
