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
    if(empty($id) || (!empty($context) && !is_array($context))) {
      throw new Exception('Invalid Arguments!',1,'Tempearly.php',39);
    }

    $SOURCE = $this->PATH.$id.$this->EXTENSION;
    if(!file_exists($SOURCE)) {
      throw new Exception('Template file "'.$SOURCE.'" doesn\'t exist or is not readable!',2,'Tempearly.php',44);
    }

    $tpl = file_get_contents($SOURCE);

    $systemContext = $this->buildContext();

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

    // Variable replacement
    $tpl = preg_replace_callback('/({{)([\w-]+)(}})/',function($matches) use ($systemContext, $context) {
      $variableName = $matches[2];

      // TODO: Add default replacement if no value could be found?
      $default = '';
      $result;

      if(strpos($variableName,'.') != false) {
        $variableName = explode('.',$variableName);

        if(is_array($context) && array_key_exists($variableName[0],$context)) {
          $result = $context;

          foreach ($variableName as $key => $value) {
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
        } elseif(array_key_exists($variableName[0],$systemContext)) {
          $result = $systemContext;

          foreach ($variableName as $key => $value) {
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
        if(is_array($context) && array_key_exists($variableName,$context)) {
          if(is_callable($context[$variableName])) {
            $result = $context[$variableName]();
          } else {
            $result = $context[$variableName];
          }
        } elseif(array_key_exists($variableName,$systemContext)) {
          if(is_callable($context[$variableName])) {
            $result = $systemContext[$variableName]();
          } else {
            $result = $systemContext[$variableName];
          }
        } else {
          $result = $default;
        }
      }

      return $result;
    },$tpl);

    // Template rendering
    $tpl = preg_replace_callback('/({{tpl\()([\w-]+)(\)}})/',function($matches) use ($context) {
      $identifier = $matches[2];

      return $this->render($identifier,$context);
    },$tpl);

    return $tpl;
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
}

?>
