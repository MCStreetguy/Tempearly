<?php

namespace MCStreetguy\Tempearly\Interfaces;

/**
 * The Invokable interface standarizes invokable objects.
 */
interface Invokable {
  /**
   * The main function of the Invokable, that can be called by the engine.
   */
  public static function invoke();
}


?>
