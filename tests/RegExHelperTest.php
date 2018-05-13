<?php

use PHPUnit\Framework\TestCase;

use MCStreetguy\Tempearly\Service\RegExHelper;

final class RegExHelperTest extends TestCase
{
  /**
   * @return void
   */
  public function testClassStructureIsValid() : void
  {
    $this->assertClassHasStaticAttribute('GENERAL', RegExHelper::class, "Class definition is missing the 'GENERAL' attribute!");
    $this->assertClassHasStaticAttribute('DELIMITER', RegExHelper::class, "Class definition is missing the 'DELIMITER' attribute!");
    $this->assertClassHasStaticAttribute('CONDITIONS', RegExHelper::class, "Class definition is missing the 'CONDITIONS' attribute!");
    $this->assertClassHasStaticAttribute('KEYWORDS', RegExHelper::class, "Class definition is missing the 'KEYWORDS' attribute!");
    $this->assertClassHasStaticAttribute('COMMENTS', RegExHelper::class, "Class definition is missing the 'COMMENTS' attribute!");
    $this->assertClassHasStaticAttribute('MINIFIER', RegExHelper::class, "Class definition is missing the 'MINIFIER' attribute!");
  }
}

?>
