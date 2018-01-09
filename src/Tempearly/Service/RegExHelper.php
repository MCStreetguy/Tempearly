<?php

namespace MCStreetguy\Tempearly\Service;

/**
 * A helper class, containing RegEx snippets and expressions for further use.
 */
abstract class RegExHelper {

  /**
   * @var array $GENERAL
   */
  public static $GENERAL = [
    "start" => "({{ ?)",
    "value" => "([\"\']?[\w-. ]+[\"\']?)",
    "end" => "( ?}})",
    "any" => "(.+)",
    "id" => "([\w-.\/\\]+)"
  ];

  /**
   * @var array $DELIMITER
   */
  public static $DELIMITER = [
    "processor" => "( ?\| ?)",
    "randomizer" => "( ?\, ?)",
    "ternary" => "( ?: ?)"
  ];

  /**
   * @var array $CONDITIONS
   */
  public static $CONDITIONS = [
    "start" => "({{if )([\w-]+)(}})",
    "end" => "(?={{\/if}})({{\/if}})",
    "body" => "([\w\W]+?)",
    "else" => "({{else}})",
    "ternary" => "( ?\? ?)"
  ];

  /**
   * @var array $KEYWORDS
   */
  public static $KEYWORDS = [
    "template" => "({{tpl ?)"
  ];

  /**
   * @var string $COMMENTS
   */
  public static $COMMENTS = "/{\*.*\*}/";

  /**
   * @var array $MINIFIER
   */
  public static $MINIFIER = [
    "whitespaceOutsideTags" => "/((?<=>)[^\S ]+|[^\S ]+(?=<))/",
    "multipleWhitespaces" => "/(\s)+\s/",
    "htmlComments" => "/<!--.*-->/"
  ];
}


?>
