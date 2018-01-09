<?php

namespace MCStreetguy\Tempearly\Interfaces;
use MCStreetguy\Tempearly\Interfaces\Invokable;

/**
 * An interface for processor objects.
 */
interface Processor extends Invokable {

  /**
   * The main function of the Processor.
   *
   * @param mixed $value The value to process
   * @param array $context The current context the processor is invoked in
   * @return mixed
   */
  public static function invoke($value, array $context);
}


?>
