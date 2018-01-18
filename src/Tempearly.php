<?php

namespace MCStreetguy;
use Exception;
use MCStreetguy\Tempearly\Context;
use MCStreetguy\Tempearly\Service\RegExHelper;

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
        $context = new Context($context);
      }
      $hasContext = true;
    } else {
      $hasContext = false;
    }

    $systemContext = $this->buildContext();

    // Comments
    $tpl = preg_replace(RegExHelper::$COMMENTS,'',$tpl);

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
    $regexp = '/'.
              RegExHelper::$CONDITIONS['start'].
              RegExHelper::$CONDITIONS['body'].
              RegExHelper::$CONDITIONS['else'].
              RegExHelper::$CONDITIONS['body'].
              RegExHelper::$CONDITIONS['end'].
              '/';
    $tpl = preg_replace_callback($regexp,$func,$tpl);

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
    $regexp = '/'.
              RegExHelper::$CONDITIONS['start'].
              RegExHelper::$CONDITIONS['body'].
              RegExHelper::$CONDITIONS['end'].
              '/';
    $tpl = preg_replace_callback($regexp,$func,$tpl);

    // Ternary operators
    $func = function ($matches) use ($hasContext, $context, $systemContext) {
      $condition = $matches[2];
      $ifVariableName = $matches[4];
      $elseVariableName = $matches[6];

      if($hasContext && $context->has($condition) && $context->has($ifVariableName) && $context->has($elseVariableName)) {
        if($context->get($condition) == true) {
          return $context->get($ifVariableName);
        } else {
          return $context->get($elseVariableName);
        }
      } elseif($systemContext->has($condition) && $systemContext->has($ifVariableName) && $systemContext->has($elseVariableName)) {
        if($systemContext->get($condition) == true) {
          return $systemContext->get($ifVariableName);
        } else {
          return $systemContext->get($elseVariableName);
        }
      } else {
        // TODO: Add default replacement if the values could not be found?
        return '';
      }
    };
    $regexp = '/'.
              RegExHelper::$GENERAL['start'].
              RegExHelper::$GENERAL['value'].
              RegExHelper::$CONDITIONS['ternary'].
              RegExHelper::$GENERAL['value'].
              RegExHelper::$DELIMITER['ternary'].
              RegExHelper::$GENERAL['value'].
              RegExHelper::$GENERAL['end'].
              '/';
    $tpl = preg_replace_callback($regexp,$func,$tpl);

    // Template rendering
    $func = function($matches) use ($context) {
      $identifier = $matches[2];

      return $this->render($identifier,$context);
    };
    $regexp = '/'.
              RegExHelper::$KEYWORDS['template'].
              RegExHelper::$GENERAL['filename'].
              RegExHelper::$GENERAL['end'].
              '/';
    $tpl = preg_replace_callback($regexp,$func,$tpl);

    // Variable replacement
    $func = function($matches) use ($hasContext,$context,$systemContext) {
      $expression = $matches[2];

      $value;

      $processorRegex = '/^'.
                        RegExHelper::$GENERAL['value'].
                        RegExHelper::$DELIMITER['processor'].
                        RegExHelper::$GENERAL['value'].
                        '/';
      if(strpos($expression,',')) {
        // Value randomization
        $expression = preg_split('/'.RegExHelper::$DELIMITER['randomizer'].'/',$expression);
        $expression = $expression[rand(0,count($expression)-1)];

        if($hasContext && $context->has($expression)) {
          $value = $context->get($expression);
        } elseif($systemContext->has($expression)) {
          $value = $systemContext->get($expression);
        } else {
          // TODO: Add default replacement if no value could be found?
          $value = '';
        }
      } elseif(preg_match_all($processorRegex,$expression) > 0) {
        // Processors set
        $func = function ($matches) use ($context,$systemContext) {
          $value = $hasContext && $context->get($matches[1]);
          $processor = $hasContext && $context->getProcessor($matches[3]);

          if($processor != false) {
            $value = $processor($value,$context);
          } else {
            $processor = $systemContext->getProcessor($matches[3]);

            if($processor != false) {
              $value = $processor($value,$systemContext);
            } else {
              // TODO: Add default replacement if no value could be found?
              $value = '';
            }
          }
        };

        do {
          $expression = preg_replace_callback($processorRegex,$func,$expression);
        } while (preg_match_all($processorRegex,$expression) > 0);

        $strip =  '/('.
                  RegExHelper::$GENERAL['start'].
                  '|'.
                  RegExHelper::$GENERAL['end'].
                  ')/';
        $value = preg_replace($strip,'',$value);

        if($hasContext && $context->has($value)) {
          $value = $context->get($value);
        } elseif($systemContext->has($value)) {
          $value = $systemContext->get($value);
        } else {
          // TODO: Add default replacement if no value could be found?
          $value = '';
        }
      } else {
        if($hasContext && $context->has($expression)) {
          $value = $context->get($expression);
        } elseif($systemContext->has($expression)) {
          $value = $systemContext->get($expression);
        } else {
          // TODO: Add default replacement if no value could be found?
          $value = '';
        }
      }

      return $value;
    };
    $regexp = '/'.
              RegExHelper::$GENERAL['start'].
              RegExHelper::$GENERAL['any'].
              RegExHelper::$GENERAL['end'].
              '/';
    $tpl = preg_replace_callback($regexp,$func,$tpl);

    return $tpl;
  }

  // Helper methods

  /**
   * Builds up the system context.
   *
   * @return Context
   */
  private function buildContext() {
   return new Context([
     '_UID' => uniqid('uid_',false)
   ]);
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
    $html = preg_replace(RegExHelper::$MINIFIER['whitespaceOutsideTags'],'',$html);
    $html = preg_replace(RegExHelper::$MINIFIER['multipleWhitespaces'],' ',$html);
    $html = preg_replace(RegExHelper::$MINIFIER['htmlComments'],'',$html);

    return $html;
  }
}

?>
