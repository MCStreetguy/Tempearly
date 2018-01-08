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
   * @param array $context [optional] Additional context variables for template processing
   * @return string The parsed template string
   */
  public function render($id,$context = null) {
    if(empty($id)) {
      throw new Exception('Invalid Arguments!',1);
    }

    $SOURCE = $this->PATH.$id.$this->EXTENSION;
    if(!file_exists($SOURCE)) {
      throw new Exception('Template file "'.$SOURCE.'" doesn\'t exist or is not readable!',2);
    }

    $tpl = file_get_contents($SOURCE);

    $hasContext;

    if(!empty($context)) {
      if(is_object($context) && get_class($context) != 'MCStreetguy\Tempearly\Context') {
        throw new Exception('Invalid Arguments!',1);
      } elseif(is_array($context)) {
        $context = new Tempearly\Context($context);
      }
      $hasContext = true;
    } else {
      $hasContext = false;
    }

    $systemContext = $this->buildContext();

    // Comments
    $tpl = preg_replace('/{\*.*\*}/','',$tpl);

    // If-Else-Conditions
    $tpl = preg_replace_callback('/({{if )([\w-]+)(}})([\w\W]+?)({{else}})([\w\W]+?)(?={{\/if}})({{\/if}})/',function($matches) use ($systemContext, $context, $hasContext) {
      $condition = $matches[2];
      $content = $matches[4];
      $alternate = $matches[6];

      if($hasContext && $context->has($condition)) {
        if($context->get($condition) == true) {
          return $content;
        } else {
          return $alternate;
        }
      } elseif($systemContext->has($condition)) {
        if($systemContext->get($condition) == true) {
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
    $tpl = preg_replace_callback('/({{if )([\w-]+)(}})([\w\W]+?)(?={{\/if}})({{\/if}})/',function($matches) use ($systemContext, $context, $hasContext) {
      $condition = $matches[2];
      $content = $matches[4];

      if($hasContext && $context->has($condition)) {
        if($context->get($condition) == true) {
          return $content;
        } else {
          return '';
        }
      } elseif($systemContext->has($condition)) {
        if($systemContext->get($condition) == true) {
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
    $tpl = preg_replace_callback('/({{ ?)(.+)( ?}})/',function($matches) use ($context) {
      $expression = $matches[2];
      
      if(strpos($expression,',')) {
        // Value randomization
        $expression = preg_split('/ ?, ?/',$expression);
        $expression = $expression[rand(0,count($expression))];
      }
      
      return $this->getValue($expression,$context);
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
   * @param array $context [optional] The user context to search in
   * @param array $systemContext [optional] The system context to search in
   * @return string The corresponding variable value
   */
  private function getValue($var,$context = null,$systemContext = null) {
    if(empty($systemContext)) {
      $systemContext = $this->buildContext();
    }

    if(!empty($context)) {
      if(is_object($context) && get_class($context) != 'MCStreetguy\Tempearly\Context') {
        throw new Exception('Invalid Arguments!',1);
      } elseif(is_array($context)) {
        $context = new Tempearly\Context($context);
      }
      $hasContext = true;
    } else {
      $hasContext = false;
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

      if($hasContext && $context->has($var[0])) {
        $result = $context->get('_all');

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
      } elseif($systemContext->has($var[0])) {
        $result = $systemContext->get('_all');

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
      if($hasContext && $context->has($var)) {
        $val = $context->get($var);

        if(is_callable($val)) {
          $result = $val();
        } else {
          $result = $val;
        }
      } elseif($systemContext->has($var)) {
        $val = $systemContext->get($var);

        if(is_callable($val)) {
          $result = $val();
        } else {
          $result = $val;
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
   * @return MCStreetguy\Tempearly\Context
   */
  private function buildContext() {
    return new Tempearly\Context(array(
      'rule' => '<hr />'
    ));
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
