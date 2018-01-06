<?php

namespace MCStreetguy\Tempearly;
use Exception;

/**
 *
 */
class Context {

  /**
   * @var array $PROTECTED
   */
  private $PROTECTED = [
    '_all'
  ];

  /**
   * @var array $CONTENTS
   */
  private $CONTENTS;

  /**
   * @param array $content The context contents
   */
  function __construct($content = null) {
    if(!empty($content) && is_array($content)) {
      $this->CONTENTS = $content;
    } else {
      $this->CONTENTS = array();
    }
  }

  /**
   * Adds a new item to the context.
   *
   * @param string $key The context-key of the new entry
   * @param mixed $value The value of the new entry
   * @throws Exception If invalid parameters are given
   * @return void
   */
  public function push($key,$value) {
    if(empty($key) || !is_string($key)) {
      throw new Exception('Invalid key parameter given!',1);
    } elseif(empty($value)) {
      throw new Exception('Invalid value parameter given!',2);
    } elseif(is_array($key,$this->PROTECTED)) {
      throw new Exception('Key "'.$key.'" is protected and can not be overridden!',3);
    } else {
      $this->CONTENTS[$key] = $value;
    }
  }

  /**
   * Get a value from the context.
   *
   * @param string $key The key to return or '_all'
   * @return mixed
   */
  public function get($key) {
    if($key == '_all') {
      return $this->CONTENTS;
    } elseif(array_key_exists($key,$this->CONTENTS)) {
      return $this->CONTENTS[$key];
    } else {
      return false;
    }
  }

  /**
   * Checks if a key exists in the context.
   *
   * @param string $key The key to search for
   * @return bool
   */
  public function has($key) {
    return ($this->get($key) != false);
  }

  /**
   * Adds a key to the protected keys configuration.
   * This cannot be undone!
   *
   * @param string $key The key to protect
   * @return void
   */
  public function protect($key) {
    $this->PROTECTED[] = $key;
  }
}

?>
