<?php

use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Error\Error;

use MCStreetguy\Tempearly\Service\RegExHelper;

final class RegExHelperTest extends TestCase
{
  /**
   * @return void
   */
  public function testCantConstructInstance() : void
  {
    $this->expectException(Error::class);

    $test = new RegExHelper();
  }

  /**
   * @return void
   */
  public function testClassStructureIsValid() : void
  {
    $this->assertClassHasStaticAttribute('GENERAL', 'RegExHelper', "Class definition is missing the 'GENERAL' attribute!");
    $this->assertClassHasStaticAttribute('DELIMITER', 'RegExHelper', "Class definition is missing the 'DELIMITER' attribute!");
    $this->assertClassHasStaticAttribute('CONDITIONS', 'RegExHelper', "Class definition is missing the 'CONDITIONS' attribute!");
    $this->assertClassHasStaticAttribute('KEYWORDS', 'RegExHelper', "Class definition is missing the 'KEYWORDS' attribute!");
    $this->assertClassHasStaticAttribute('COMMENTS', 'RegExHelper', "Class definition is missing the 'COMMENTS' attribute!");
    $this->assertClassHasStaticAttribute('MINIFIER', 'RegExHelper', "Class definition is missing the 'MINIFIER' attribute!");
  }
}

?>
