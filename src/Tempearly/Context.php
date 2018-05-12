<?php

namespace MCStreetguy\Tempearly;
use MCStreetguy\Tempearly\Interfaces\Processor;

/**
 * The context class. Contains variables (keys with values) to use within the template.
 *
 * @author Maximilian Schmidt <maximilianschmidt404@gmail.com>
 * @license MIT
 */
class Context {

  /**
   * @var array $protected Contains protected key names
   */
  protected $protected = [
    '_all'
  ];

  /**
   * @var array $contents The context contents
   * @var array $processors The context processors
   */
  protected $contents = [], $processors = [];

  /**
   * Initiates a new context object.
   *
   * @param array|null $content [optional] The context contents
   * @param array|null $processors [optional] The context processors
   */
  function __construct(array $content = null, array $processors = null) {
    if(!empty($content) && is_array($content)) {
      $this->expand($content);
    }

    if(!empty($processors) && is_array($processors)) {
      $this->registerAll($processors);
    }
  }

  /**
   * Adds a new item to the context.
   *
   * @param string $key The context-key of the new entry
   * @param mixed $value The value of the new entry
   * @return bool
   */
  public function push(string $key,$value) : bool
  {
    if(empty($key) || !is_string($key) || empty($value) || in_array($key,$this->protected)) {
      return false;
    } else {
      if(strpos($key,'.') != false) {
        $key = explode('.',$key);

        if($this->has($key[0])) {
          $result = $this->contents;
          $query = '$this->contents';

          for ($i=0; $i < count($key) - 1; $i++) {
            $query .= '["'.$key[$i].'"]';

            if(is_array($result) && array_key_exists($key[$i],$result)) {
              $result = $result[$key[$i]];
            } else {
              return false;
            }
          }

          $query .= '["'.$key[count($key)-1].'"] = $value;';

          eval($query);
        } else {
          return false;
        }
      } else {
        $this->contents[$key] = $value;
      }

      return true;
    }
  }

  /**
   * Adds an array of entries to the context.
   *
   * @param array $entries The key-value-pairs to add
   * @return int
   */
  public function expand(array $entries) : int
  {
    $result = 0;
    foreach ($entries as $key => $value) {
      if($this->push($key,$value)) {
        $result++;
      }
    }

    return $result;
  }

  /**
   * Get a value from the context.
   *
   * @param string $key The keypath to return or '_all'
   * @param bool $forceString
   * @return mixed
   */
  public function get(string $key, bool $forceString = false)
  {
    $default = '';

    if(strtolower($key) == '_all') {
      $result = $this->contents;
    } elseif(strpos($key,'.') != false) {
      $key = explode('.',$key);

      if($this->has($key[0])) {
        $result = $this->contents;

        foreach ($key as $key => $value) {
          if(is_array($result) && array_key_exists($value,$result)) {
            $result = $result[$value];
          } else {
            $result = $default;
            break;
          }
        }
      } else {
        $result = $default;
      }
    } else {
      if($this->has($key)) {
        $result = $this->contents[$key];
      } else {
        $result = $default;
      }
    }

    return $result;
  }

  /**
   * Checks if a key exists in the context.
   *
   * @param string $key The key to search for
   * @return bool
   */
  public function has(string $key) : bool
  {
    if(strpos($key,'.') != false) {
      $key = explode('.',$key);

      if(array_key_exists($key[0],$this->contents)) {
        $result = $this->contents;

        foreach ($key as $key => $value) {
          if(is_array($result) && array_key_exists($value,$result)) {
            $result = $result[$value];
          } else {
            $result = false;
            break;
          }
        }

        $result = ($result != false);
      } else {
        $result = false;
      }
    } else {
      $result = array_key_exists($key,$this->contents);
    }

    return $result;
  }

  /**
   * Adds a key to the protected keys configuration.
   * This cannot be undone!
   *
   * @param string $key The key to protect
   * @return void
   */
  public function protect(string $key) : void
  {
    $this->protected[] = $key;
  }

  /**
   * Gets a processor from the context.
   *
   * @param string $name Name of the processor to get
   * @return Processor|bool
   */
  public function getProcessor(string $name)
  {
    if(!empty($name) && array_key_exists($name,$this->processors)) {
      return $this->processors[$name];
    } else {
      return false;
    }
  }

  /**
   * Registers a new processor on the context.
   *
   * @param string $name The name of the processor (must be unique)
   * @param Processor $processor The processor function
   * @param bool $force [optional] Allow overriding of existing processors (not recommended)
   * @return bool
   */
  public function register(string $name, Processor $processor, bool $force = false) : bool
  {
    if(!array_key_exists($name,$this->processors) || $force) {
      $this->processors[$name] = $processor;
      return true;
    } else {
      return false;
    }
  }

  /**
   * Registers multiple processors at once.
   *
   * @param array $processors The processors to register
   * @return int
   */
  public function registerAll(array $processors) : int
  {
    $result = 0;
    foreach ($processors as $key => $value) {
      if($this->register($key,$value)) {
        $result++;
      }
    }

    return $result;
  }

  /**
   * Tries to parse a plain value.
   *
   * @param string $valueExpression The value expression to parse
   * @param bool $forceString Force conversion of value to string
   * @return mixed
   */
  public function parseValue(string $valueExpression, bool $forceString = false) {
    $result;

    if($forceString) {
      if(strtolower($valueExpression) == 'true') {
        $result = 'true';
      } elseif(strtolower($valueExpression) == 'false') {
        $result = 'false';
      } elseif(floatval($valueExpression) && floatval($valueExpression) != intval($valueExpression)) {
        $result = (string)floatval($valueExpression);
      } elseif(intval($valueExpression) && floatval($valueExpression) == intval($valueExpression)) {
        $result = (string)intval($valueExpression);
      } elseif(preg_match('/(["\'])([^"\']*)(["\'])/',$valueExpression,$result)) {
        $result = $result[0];
      } else {
        $result = "\"".$valueExpression."\"";
      }
    } else {
      if(strtolower($valueExpression) == 'true') {
        $result = true;
      } elseif(strtolower($valueExpression) == 'false') {
        $result = false;
      } elseif(floatval($valueExpression) && floatval($valueExpression) != intval($valueExpression)) {
        $result = floatval($valueExpression);
      } elseif(intval($valueExpression) && floatval($valueExpression) == intval($valueExpression)) {
        $result = intval($valueExpression);
      } elseif(preg_match('/(["\'])([^"\']*)(["\'])/',$valueExpression,$result)) {
        $result = $result[2];
      } else {
        $result = null;
      }
    }

    return $result;
  }
}

?>
