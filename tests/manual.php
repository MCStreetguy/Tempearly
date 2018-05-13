<?php

include __DIR__ . '/../vendor/autoload.php';

use MCStreetguy\Tempearly\Context;

$context = new Context();

$func1 = function (string $value) {
  return 'Hello World!';
};

$func2 = function (string $value) {
  return strtoupper($value);
};

$count = $context->registerAll([
  'testing' => $func1,
  'toupper' => $func2
]);

echo "Registered 2 processors, summary is: $count";

?>
