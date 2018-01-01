<?php

/**
 * The main class of Tempearly rendering engine.
 *
 * @author Maximilian Schmidt <maximilianschmidt404@gmail.com>
 * @license MIT
 */
abstract class Tempearly {

  /**
   * Invokes the rendering process of the given template.
   *
   * @param string $id The template identifier
   * @param array $context [optional] Additional context variables for template processing
   * @return string The parsed template string
   */
  public static function invoke($id,$context = null) {
    if(empty($id) || (!empty($context) && !is_array($context))) {
      throw new Exception('Invalid Arguments!');
    }

    $SOURCE = BASE.'/tpl/'.$id.'.tpl.html';
    if(!file_exists($SOURCE)) {
      throw new Exception('Template file "'.$SOURCE.'" doesn\'t exist or is not readable!');
    }

    $tpl = file_get_contents($SOURCE);

    $systemContext = Tempearly::_buildContext();

    $parsedTpl = preg_replace_callback('/({{)([\w-]+)(}})/',function($matches) use ($systemContext, $context) {
      $variableName = $matches[2];

      if(is_array($context) && array_key_exists($variableName,$context)) {
        return $context[$variableName];
      } else if(array_key_exists($variableName,$systemContext)) {
        return $systemContext[$variableName];
      } else {
        // TODO: Add default replacement if no value could be found?
      }
    },$tpl);

    return $parsedTpl;
  }

  /**
   *
   */
  private static function _buildContext() {
    $C = array(
      'rule' => '<hr />'
    );

    return $C;
  }
}

?>
