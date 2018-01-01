<?php

/**
 * The main class of Tempearly rendering engine.
 *
 * @author Maximilian Schmidt <maximilianschmidt404@gmail.com>
 * @license MIT
 */
class Tempearly {

  private $PATH, $EXTENSION;

  /**
   * Creates a new Tempearly instance.
   *
   * @param string $path The template folder path
   * @param string $extension [optional] The template file extension
   */
  public function __construct($path, $extension = '.tpl.html') {
    $this->$PATH = $path;
    $this->$EXTENSION = $extension;
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
      throw new Exception('Invalid Arguments!');
    }

    $_this = $this;

    $SOURCE = $_this->$PATH.$id.$_this->$EXTENSION;
    if(!file_exists($SOURCE)) {
      throw new Exception('Template file "'.$SOURCE.'" doesn\'t exist or is not readable!');
    }

    $tpl = file_get_contents($SOURCE);

    $systemContext = $_this->_buildContext();

    $tpl = preg_replace_callback('/({{if )([\w-]+)(}})([\w\W\n]+)({{\/if}})/',function($matches) use ($systemContext, $context) {
      $condition = $matches[2];
      $content = $matches[4];
      $conditionType = gettype($condition);
    },$tpl);

    $tpl = preg_replace_callback('/({{)([\w-]+)(}})/',function($matches) use ($systemContext, $context) {
      $variableName = $matches[2];

      if(is_array($context) && array_key_exists($variableName,$context)) {
        return $context[$variableName];
      } else if(array_key_exists($variableName,$systemContext)) {
        return $systemContext[$variableName];
      } else {
        // TODO: Add default replacement if no value could be found?
        return '';
      }
    },$tpl);

    $tpl = preg_replace_callback('/({{tpl\()([\w-]+)(\)}})/',function($matches) use ($_this, $context) {
      $identifier = $matches[2];

      return $_this->render($identifier,$context);
    },$tpl);

    return $tpl;
  }

  /**
   * Builds up the system context.
   *
   * @return array The system context
   */
  private function _buildContext() {
    $C = array(
      'rule' => '<hr />'
    );

    return $C;
  }
}

?>
