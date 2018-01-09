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
    $func = function($matches) use ($systemContext, $context, $hasContext) {
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
        return $alternate;
      }
    };
    $tpl = preg_replace_callback('/({{if )([\w-]+)(}})([\w\W]+?)({{else}})([\w\W]+?)(?={{\/if}})({{\/if}})/',$func,$tpl);

    // If-Conditions
    $func = function($matches) use ($systemContext, $context, $hasContext) {
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
    };
    $tpl = preg_replace_callback('/({{if )([\w-]+)(}})([\w\W]+?)(?={{\/if}})({{\/if}})/',$func,$tpl);

    // Ternary operators
    $func = function ($matches) use ($context) {
      $condition = $matches[2];
      $ifVariableName = $matches[4];
      $elseVariableName = $matches[6];

      if($context->get($condition) == true) {
        return $context->get($ifVariableName);
      } else {
        return $context->get($elseVariableName);
      }
    };
    $tpl = preg_replace_callback('/({{)([\w-.]+)( ?\? ?)([\w-.\"\']+)( ?: ?)([\w-.\"\']+)(}})/',$func,$tpl);

    // Variable replacement
    $func = function($matches) use ($context) {
      $expression = $matches[2];

      $value;

      if(strpos($expression,',')) {
        // Value randomization
        $expression = preg_split('/ ?, ?/',$expression);
        $expression = $expression[rand(0,count($expression))];

        $value = $context->get($expression);
      } elseif(preg_match_all('/^([\w-.]+)( ?\| ?)([\w-.]+)/',$expression) > 0) {
        // Processors set
        $func = function ($matches) use ($context) {
          $value = $context->get($parts[1]);
          $processor = $matches[3];

          // TODO !
        };

        do {
          $expression = preg_replace_callback('/^([\w-.]+)( ?\| ?)([\w-.]+)/',$func,$expression);
        } while (preg_match_all('/^([\w-.]+)( ?\| ?)([\w-.]+)/',$expression));
      } else {
        $value = $context->get($expression);
      }

      return $value;
    };
    $tpl = preg_replace_callback('/({{ ?)(.+)( ?}})/',$func,$tpl);

    // Template rendering
    $func = function($matches) use ($context) {
      $identifier = $matches[2];

      return $this->render($identifier,$context);
    };
    $tpl = preg_replace_callback('/({{tpl ?)([\w-]+)( ?}})/',$func,$tpl);

    return $tpl;
  }

  // Helper methods

  /**
   * Builds up the system context.
   *
   * @return Tempearly\Context
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
    // Unecessary whitespaces outside of tags
    $html = preg_replace('/((?<=>)[^\S ]+|[^\S ]+(?=<))/','',$html);
    // Unecessary whitespaces within tags
    $html = preg_replace('/(\s)+\s/',' ',$html);
    // HTML comments
    $html = preg_replace('/<!--.*-->/','',$html);

    return $html;
  }
}

?>
