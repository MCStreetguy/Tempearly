<?php

use PHPUnit\Framework\TestCase;

use MCStreetguy\Tempearly;

/**
 *
 */
final class TempearlyTest extends TestCase
{
  /**
   * @return Tempearly
   */
  public function testCanBeCreated() : Tempearly
  {
    $path = __DIR__ . '/data/';
    $engine = new Tempearly($path,'.html');

    $this->assertInstanceOf(Tempearly::class, $engine, "Could not create engine instance!");
    $this->assertEquals($path, $engine->getPath(), "Engine path contained unexpected value!");
    $this->assertAttributeEquals($path, 'PATH', $engine, "Engine attribute 'PATH' contained unexpected value!");
    $this->assertEquals('.html', $engine->getExtension(), "Engine extension contained unexpected value!");
    $this->assertAttributeEquals('.html', 'EXTENSION', $engine, "Engine attribute 'EXTENSION' contained unexpected value!");

    return $engine;
  }

  /**
   * @param Tempearly $engine
   * @depends testCanBeCreated
   * @return Tempearly
   */
  public function testGetterAndSetter(Tempearly $engine) : Tempearly
  {
    $path = __DIR__ . '/data/templates/';

    $engine->setPath($path);
    $this->assertEquals($path, $engine->getPath(), "Engine path contained unexpected value!");
    $this->assertAttributeEquals($path, 'PATH', $engine, "Engine attribute 'PATH' contained unexpected value!");

    $engine->setExtension('.tpl.html');
    $this->assertEquals('.tpl.html', $engine->getExtension(), "Engine extension contained unexpected value!");
    $this->assertAttributeEquals('.tpl.html', 'EXTENSION', $engine, "Engine attribute 'EXTENSION' contained unexpected value!");

    return $engine;
  }

  /**
   * @param Tempearly $engine
   * @depends testGetterAndSetter
   * @return Tempearly
   */
  public function testTemplateRendering(Tempearly $engine) : Tempearly
  {
    $this->assertFileExists(__DIR__ . '/data/templates/main.tpl.html', "Test template 'main' is missing!");
    $this->assertFileIsReadable(__DIR__ . '/data/templates/main.tpl.html', "Test template 'main' is not readable!");
    $this->assertFileExists(__DIR__ . '/data/templates/headerpartial.tpl.html', "Test partial 'headerpartial' is missing!");
    $this->assertFileIsReadable(__DIR__ . '/data/templates/headerpartial.tpl.html', "Test partial 'headerpartial' is not readable!");

    $template = $engine->render('main', [
      'title' => 'Test Template',
      'content' => [
        'headline' => 'This is a test',
        'text' => "When the pimp's in the crib, ma, Drop it like it's hot, drop it like it's hot, drop it like it's hot!"
      ],
      'conditions' => [
        'showSpacer' => true,
        'userLoggedIn' => false,
        'lovely' => 1
      ]
    ]);

    $this->assertInternalType('string', $template, "Template rendering did not return a string.");
    $this->assertNotEmpty($template, "Rendered template string is empty!");

    $this->assertFileExists(__DIR__ . '/data/results/main.html', "Comparison file 'main.html' is missing!");
    $this->assertFileIsReadable(__DIR__ . '/data/results/main.html', "Comparison file 'main.html' is not readable!");

    $contents = file_get_contents(__DIR__ . '/data/results/main.html');
    $this->assertEquals($contents, $template, "Rendered template is not equal to comparison file!");

    return $engine;
  }
}


?>
