<?php

use PHPUnit\Framework\TestCase;

use MCStreetguy\Tempearly\Context;

final class ContextTest extends TestCase
{
  /**
   * @return Context
   */
  public function testCanBeCreatedEmpty() : Context
  {
    $context = new Context();
    $this->assertInstanceOf(Context::class, $context);

    return $context;
  }

  /**
   * @return Context
   */
  public function testCanBeCreatedWithContent() : Context
  {
    $context = new Context([
      'Hello' => 'World',
      'foo' => 'bar',
      42 => false
    ]);
    $this->assertInstanceOf(Context::class, $context);

    return $context;
  }

  /**
   * @return Context
   */
  public function testCanBeCreatedWithProcessors() : Context
  {
    $context = new Context([], [
      'test' => function (string $value) {
        return 'Hello World!';
      },
      'toUpper' => function (string $value) {
        return strtoupper($value);
      }
    ]);
    $this->assertInstanceOf(Context::class, $context);

    return $context;
  }

  /**
   * @return Context
   */
  public function testCanBeCreatedWithContentAndProcessors() : Context
  {
    $context = new Context([
      'Hello' => 'World',
      'foo' => 'bar',
      42 => false
    ],[
      'test' => function (string $value) {
        return 'Hello World!';
      },
      'toUpper' => function (string $value) {
        return strtoupper($value);
      }
    ]);
    $this->assertInstanceOf(Context::class, $context);

    return $context;
  }

  /**
   * @param Context $context
   * @depends testCanBeCreatedEmpty
   * @return Context
   */
  public function testCanPushValues(Context $context) : Context
  {
    $context->push('hello-world', 27);

    $this->assertArrayHasKey('hello-world', $context->get('_all'));

    return $context;
  }

  /**
   * @param Context $context
   * @depends testCanPushValues
   * @return Context
   */
  public function testCanCheckForKey(Context $context) : Context
  {
    $this->assertEquals(true, $context->has('hello-world'));

    return $context;
  }

  /**
   * @param Context $context
   * @depends testCanPushValues
   * @return Context
   */
  public function testCanGetValues(Context $context) : Context
  {
    $this->assertEquals(27, $context->get('hello-world'));

    return $context;
  }
}

?>
