<?php

namespace MCStreetguy;
use Error;
use Exception;
use InvalidArgumentException;
use MCStreetguy\Tempearly\Context;
use MCStreetguy\Tempearly\Errors\InvalidSyntaxError;

use MCStreetguy\Tempearly\Service\RegExHelper;
use MCStreetguy\Tempearly\Service\SyntaxParser;
use MCStreetguy\Tempearly\Exceptions\FileSystemException;
use MCStreetguy\Tempearly\Exceptions\InvalidSyntaxException;

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
  public function __construct(string $path, string $extension = '.tpl.html')
  {
    $this->setPath($path);
    $this->setExtension($extension);
  }

  /**
   * Invokes the rendering process of the given template.
   *
   * @param string $id The template identifier
   * @param array|Context|null $context [optional] Additional context variables for template processing
   * @throws InvalidArgumentException
   * @throws FileSystemException
   * @throws InvalidSyntaxException
   * @return string The parsed template string
   */
  public function render(string $id, $context = null) : string
  {
    if(empty($id)) {
      throw new InvalidArgumentException('$id cannot be empty!',1526135454);
    }

    $SOURCE = $this->PATH.$id.$this->EXTENSION;
    if(!file_exists($SOURCE)) {
      throw new FileSystemException("Template file '$SOURCE' doesn't exist or is not readable!", 1526135618);
    }

    $tpl = file_get_contents($SOURCE);

    return $this->parse($tpl, $context);
  }

  /**
   * Renders the given source string.
   *
   * @param string $source The template source
   * @param array|Context|null $context [optional] Additional context variables for template processing
   * @throws InvalidArgumentException
   * @throws FileSystemException
   * @throws InvalidSyntaxException
   * @return string The parsed template string
   */
  public function parse(string $source, $context = null) : string
  {
    if(!empty($context)) {
      if(is_object($context) && $context instanceof Context) {
        // Do nothing
      } elseif(is_array($context)) {
        $context = new Context($context);
      } else {
        throw new InvalidArgumentException('$context is no instance of class Context and could not be converted!',1526135710);
      }

      $context->expand(self::buildContext());
    } else {
      $context = self::buildContext();
    }

    try {

      $source = SyntaxParser::parseComments($source, $context);
      $source = SyntaxParser::parseConditions($source, $context);

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
      $source = preg_replace_callback($regexp,$func,$source);

      $source = SyntaxParser::parseVariables($source, $context);

    } catch(Exception $e) {
      throw new InvalidSyntaxException('An error occurred while parsing the template string!', 1526135868, $e);
    } catch(Error $e) {
      throw new InvalidSyntaxError('An error occurred while parsing the template string!', 1526222794, $e);
    }

    return $source;
  }

  // Helper methods

  /**
   * Builds up the system context.
   *
   * @return Context
   */
  private static function buildContext() : Context
  {
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
  public function getPath() : string
  {
    return $this->PATH;
  }

  /**
   * Sets the template folder path.
   *
   * @param string $path The new template folder path
   */
  public function setPath(string $path) : void
  {
    $this->PATH = $path;
  }

  /**
   * Returns the template file extension.
   *
   * @return string The template folder path
   */
  public function getExtension() : string
  {
    return $this->EXTENSION;
  }

  /**
   * Sets the template file extension.
   *
   * @param string $extension The new template folder path
   */
  public function setExtension(string $extension) : void
  {
    $this->EXTENSION = $extension;
  }

  // Static methods

  /**
   * Minifies a html string.
   *
   * @param string $html The html to minify
   * @return string The minified html
   */
  public static function minify(string $html) : string
  {
    $html = preg_replace(RegExHelper::$MINIFIER['whitespaceOutsideTags'],'',$html);
    $html = preg_replace(RegExHelper::$MINIFIER['multipleWhitespaces'],' ',$html);
    $html = preg_replace(RegExHelper::$MINIFIER['htmlComments'],'',$html);

    return $html;
  }
}

?>
