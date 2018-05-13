<?php

namespace MCStreetguy\Tempearly\Errors;
use Error;

/**
 *
 */
class InvalidSyntaxError extends Error {

  public function __construct($message, $code = 0, Error $previous = null) {
    parent::__construct($message, $code, $previous);
  }

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}


?>
