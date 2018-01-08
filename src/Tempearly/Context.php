<?php

namespace MCStreetguy\Tempearly;
use Exception;

/**
 * The context class. Contains variables (keys with values) to use within the template.
 *
 * @author Maximilian Schmidt <maximilianschmidt404@gmail.com>
 * @license MIT
 */
class Context {

  /**
   * @var array $PROTECTED Contains protected key names
   */
  private $PROTECTED = [
    '_all'
  ];

  /**
   * @var array $CONTENTS The context contents
   * @var array $PROCESSORS The context processors
   */
  private $CONTENTS, $PROCESSORS;

  /**
   * Initiates a new context object.
   *
   * @param array|null $content [optional] The context contents
   * @param array|null $processors [optional] The context processors
   */
  function __construct(array $content = null, array $processors = null) {
    if(!empty($content) && is_array($content)) {
      $this->CONTENTS = $content;
    } else {
      $this->CONTENTS = array();
    }

    if(!empty($processors) && is_array($processors)) {
      $this->PROCESSORS = $processors;
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
  public function push(string $key,$value) {
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
  public function get(string $key) {
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
  public function has(string $key) {
    return ($this->get($key) != false);
  }

  /**
   * Adds a key to the protected keys configuration.
   * This cannot be undone!
   *
   * @param string $key The key to protect
   * @return void
   */
  public function protect(string $key) {
    $this->PROTECTED[] = $key;
  }

  /**
   * Gets a processor from the context.
   *
   * @param string $name Name of the processor to get
   * @return callable|bool
   */
  public function getProcessor(string $name) {
    if(!empty($name) && array_key_exists($name,$this->PROCESSORS)) {
      return $this->PROCESSORS->$name;
    } else {
      return false;
    }
  }

  /**
   * Registers a new processor on the context.
   *
   * @param string $name The name of the processor (must be unique)
   * @param callable $processor The processor function
   * @param bool $force [optional] Allow overriding of existing processors (not recommended)
   * @return bool
   */
  public function register(string $name, callable $processor, bool $force = false) {
    if(!array_key_exists($name,$this->PROCESSORS) || $force) {
      $this->PROCESSORS[$name] = $processor;
      return true;
    } else {
      return false;
    }
  }
}

?>
