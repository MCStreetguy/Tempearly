# Tempearly
A tiny PHP templating engine.

### Installation
Require it through composer:   
```
$ composer require mcstreetguy/tempearly
```

### Usage
Initiate a new Tempearly instance by providing the template folder path and optionally a file extension:

```PHP
$engine = new MCStreetguy\Tempearly('path/to/templates');
```

The file extension defaults to '.tpl.html', the path argument has to be set.

---

Then invoke the rendering process as following:

```PHP
$engine->parse('login-template-1',array(
  'my-variable' => 'Hello World!',
  'another-variable' => true
));
```

The second argument is optional and provides context-variables to the rendering process.
The function returns the parsed template as string. Echo it directly to the page or work further on it, as you wish.
