<?php

namespace MCStreetguy;
use Exception;

/**
 * The main class of Tempearly rendering engine.
 *
 * @author Maximilian Schmidt <maximilianschmidt404@gmail.com>
 * @license MIT
 */
class Tempearly {

  /**
   * @var $PATH The template folder path
   * @var $EXTENSION The template file extension
   */
  private $PATH, $EXTENSION;

  /**
   * Creates a new Tempearly instance.
   *
   * @param string $path The template folder path
   * @param string $extension [optional] The template file extension
   */
  public function __construct($path, $extension = '.tpl.html') {
    $this->setPath($path);
    $this->setExtension($extension);
  }

  /**
   * Invokes the rendering process of the given template.
   *
   * @param string $id The template identifier
   * @param mixed $context [optional] Additional context variables for template processing
   * @return string The parsed template string
   */
  public function render(string $id,$context = null) {
    if(empty($id) || (!empty($context) && !is_array($context))) {
      throw new Exception('Invalid Arguments!',1);
    }

    $SOURCE = $this->PATH.$id.$this->EXTENSION;
    if(!file_exists($SOURCE)) {
      throw new Exception('Template file "'.$SOURCE.'" doesn\'t exist or is not readable!',2);
    }

    $tpl = file_get_contents($SOURCE);

    if(!empty($context)) {
      if(is_object($context)) {
        d(get_class($context));
        die();
      } elseif(is_array($context)) {
        $context = new Tempearly\Context($context);
        d($context);
        die();
      }
    }

    $systemContext = $this->buildContext();

    // Comments
    $tpl = preg_replace('/{\*.*\*}/','',$tpl);

    // If-Else-Conditions
    $tpl = preg_replace_callback('/({{if )([\w-]+)(}})([\w\W]+?)({{else}})([\w\W]+?)(?={{\/if}})({{\/if}})/',function($matches) use ($systemContext, $context) {
      $condition = $matches[2];
      $content = $matches[4];
      $alternate = $matches[6];

      if(is_array($context) && array_key_exists($condition,$context)) {
        if($context[$condition] == true) {
          return $content;
        } else {
          return $alternate;
        }
      } elseif(array_key_exists($condition,$systemContext)) {
        if($systemContext[$condition] == true) {
          return $content;
        } else {
          return $alternate;
        }
      } else {
        // TODO: Add default replacement if no value could be found?
        return $alternate;
      }
    },$tpl);

    // If-Conditions
    $tpl = preg_replace_callback('/({{if )([\w-]+)(}})([\w\W]+?)(?={{\/if}})({{\/if}})/',function($matches) use ($systemContext, $context) {
      $condition = $matches[2];
      $content = $matches[4];

      if(is_array($context) && array_key_exists($condition,$context)) {
        if($context[$condition] == true) {
          return $content;
        } else {
          return '';
        }
      } elseif(array_key_exists($condition,$systemContext)) {
        if($systemContext[$condition] == true) {
          return $content;
        } else {
          return '';
        }
      } else {
        // TODO: Add default replacement if no value could be found?
        return '';
      }
    },$tpl);

    // Ternary operators
    $tpl = preg_replace_callback('/({{)([\w-.]+)( ?\? ?)([\w-.\"\']+)( ?: ?)([\w-.\"\']+)(}})/',function ($matches) use ($context) {
      $condition = $matches[2];
      $ifVariableName = $matches[4];
      $elseVariableName = $matches[6];

      if($this->getValue($condition,$context) == true) {
        return $this->getValue($ifVariableName,$context);
      } else {
        return $this->getValue($elseVariableName,$context);
      }
    },$tpl);

    // Variable replacement
    $tpl = preg_replace_callback('/({{ ?)([\w-.]+)( ?}})/',function($matches) use ($context) {
      return $this->getValue($matches[2],$context);
    },$tpl);

    // Template rendering
    $tpl = preg_replace_callback('/({{tpl ?)([\w-]+)( ?}})/',function($matches) use ($context) {
      $identifier = $matches[2];

      return $this->render($identifier,$context);
    },$tpl);

    return $tpl;
  }

  // Helper methods

  /**
   * Searches a variable in all contexts.
   *
   * @param string $var The variable name to search for
   * @param array $context The user context to search in
   * @param array $systemContext [optional] The system context to search in
   * @return string The corresponding variable value
   */
  private function getValue($var,$context,$systemContext) {
    if(empty($systemContext)) {
      $systemContext = $this->buildContext();
    }

    $result;
    $matches;

    // TODO: Add default replacement if no value could be found
    $default = '';

    if(strtolower($var) == 'true') {
      $result = true;
    } elseif(strtolower($var) == 'false') {
      $result = false;
    } elseif(preg_match_all('/([\"\'])([^\"\']*)([\"\'])/',$var,$matches) > 0) {
      $result = $matches[2][0];
    } elseif(strpos($var,'.') != false) {
      $var = explode('.',$var);

      if(is_array($context) && array_key_exists($var[0],$context)) {
        $result = $context;

        foreach ($var as $key => $value) {
          if(is_array($result) && array_key_exists($value,$result)) {
            $result = $result[$value];
          } else {
            $result = $default;
            break;
          }
        }

        if(is_callable($result)) {
          $result = $result();
        }
      } elseif(array_key_exists($var[0],$systemContext)) {
        $result = $systemContext;

        foreach ($var as $key => $value) {
          if(is_array($result) && array_key_exists($value,$result)) {
            $result = $result[$value];
          } else {
            $result = $default;
            break;
          }
        }

        if(is_callable($result)) {
          $result = $result();
        }
      }
    } else {
      if(is_array($context) && array_key_exists($var,$context)) {
        if(is_callable($context[$var])) {
          $result = $context[$var]();
        } else {
          $result = $context[$var];
        }
      } elseif(array_key_exists($var,$systemContext)) {
        if(is_callable($context[$var])) {
          $result = $systemContext[$var]();
        } else {
          $result = $systemContext[$var];
        }
      } else {
        $result = $default;
      }
    }

    return $result;
  }

  /**
   * Builds up the system context.
   *
   * @return array The system context
   */
  private function buildContext() {
    return array(
      'rule' => '<hr />'
    );
  }

  // Getter & Setter

  /**
   * Returns the template folder path.
   *
   * @return string The template folder path
   */
  public function getPath() {
    return $this->PATH;
  }

  /**
   * Sets the template folder path.
   *
   * @param string $path The new template folder path
   */
  public function setPath($path) {
    $this->PATH = $path;
  }

  /**
   * Returns the template file extension.
   *
   * @return string The template folder path
   */
  public function getExtension() {
    return $this->EXTENSION;
  }

  /**
   * Sets the template file extension.
   *
   * @param string $extension The new template folder path
   */
  public function setExtension($extension) {
    $this->EXTENSION = $extension;
  }

  // Static methods

  /**
   * Minifies a html string.
   *
   * @param string $html The html to minify
   * @return string The minified html
   */
  public static function minify($html) {
    $html = preg_replace('/((?<=>)[^\S ]+|[^\S ]+(?=<))/','',$html);
    return preg_replace('/(\s)+\s/',' ',$html);
  }
}

?>
