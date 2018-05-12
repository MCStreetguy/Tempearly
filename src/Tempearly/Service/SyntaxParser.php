<?php

namespace MCStreetguy\Tempearly\Service;
use MCStreetguy\Tempearly\Context;
use MCStreetguy\Tempearly\Service\RegExHelper;

/**
 *
 */
abstract class SyntaxParser
{
  /**
   * @param string $source
   * @return string
   */
  public static function parseComments(string $source) : string
  {
    return preg_replace(RegExHelper::$COMMENTS,'',$tpl);
  }

  /**
   * @param string $source
   * @param Context $context
   * @return string
   */
  public static function parseConditions(string $source, Context $context) : string
  {
    $source = self::parseIfElse($source);
    $source = self::parseIf($source);
    $source = self::parseTernary($source);

    return $source;
  }

  /**
   * @param string $source
   * @param Context $context
   * @return string
   */
  public static function parseVariables(string $source, Context $context) : string
  {
    $func = function($matches) use ($context) {
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

        if($context->has($expression)) {
          $value = $context->get($expression);
        } else {
          // TODO: Add default replacement if no value could be found?
          // TODO: Retry randomization if no value could be found?
          $value = '';
        }
      } elseif(preg_match_all($processorRegex,$expression) > 0) {
        // Processors set
        $func = function ($matches) use ($context) {
          $value = $context->get($matches[1]);
          $processor = $context->getProcessor($matches[3]);

          if($processor != false) {
            $value = $processor($value,$context);
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

        if($context->has($value)) {
          $value = $context->get($value);
        } else {
          // TODO: Add default replacement if no value could be found?
          $value = '';
        }
      } else {
        if($context->has($expression)) {
          $value = $context->get($expression);
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
    return preg_replace_callback($regexp,$func,$source);
  }

  /**
   * @param string $expression
   * @param Context $context
   * @return mixed
   */
  public function parseExpression(string $expression, Context $context)
  {
    $values = [];
    preg_match_all('/([\w.]|((?<! )-(?! )))+/',$expression,$values);

    foreach ($values[0] as $value) {
      if($context->has($value)) {
        $res = $context->get($value,true);
      } else {
        $res = 'null';
      }

      $expression = str_replace($value,$res,$expression);
    }

    return eval("return ($expression)");
  }

  /**
   * @param string $source
   * @param Context $context
   * @return string
   */
  protected static function parseIfElse(string $source, Context $context) : string
  {
    $func = function($matches) use ($context) {
      $condition = $matches[2];
      $content = $matches[4];
      $alternate = $matches[6];

      if($context->has($condition)) {
        if($context->get($condition) == true) {
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
    return preg_replace_callback($regexp,$func,$source);
  }

  /**
   * @param string $source
   * @param Context $context
   * @return string
   */
  protected static function parseIf(string $source, Context $context) : string
  {
    $func = function($matches) use ($context) {
      $condition = $matches[2];
      $content = $matches[4];

      if($context->has($condition)) {
        if($context->get($condition) == true) {
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
    return preg_replace_callback($regexp,$func,$source);
  }

  /**
   * @param string $source
   * @param Context $context
   * @return string
   */
  protected static function parseTernary(string $source, Context $context) : string
  {
    $func = function ($matches) use ($context) {
      $condition = $matches[2];
      $ifVariableName = $matches[4];
      $elseVariableName = $matches[6];

      if($context->has($condition) && $context->has($ifVariableName) && $context->has($elseVariableName)) {
        if($context->get($condition) == true) {
          return $context->get($ifVariableName);
        } else {
          return $context->get($elseVariableName);
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
    return preg_replace_callback($regexp,$func,$source);
  }
}


?>
