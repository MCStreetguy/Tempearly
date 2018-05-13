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
      '42' => false
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
      '42' => false
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
    $this->assertTrue($context->push('hello-world', 27), "Could not push 'hello-world => 27' to context!");
    $this->assertTrue($context->has('hello-world'), "The context does not contain the previously stored value 'hello-world => 27'!");
    $this->assertAttributeContains(27, 'contents', $context, "The context contents attribute does not contain the previously stored value 'hello-world => 27'!");

    return $context;
  }

  /**
   * @param Context $context
   * @depends testCanPushValues
   * @return Context
   */
  public function testCanExpand(Context $context) : Context
  {
    $this->assertEquals(3, $context->expand([
      'Hello' => 'World',
      'foo' => 'bar',
      '42' => false
    ]), "Expanding of context returned unexpected summary value!");

    $this->assertTrue($context->has('Hello'), "The context does not contain the previously stored value 'Hello => World'!");
    $this->assertAttributeContains('World', 'contents', $context, "The context contents attribute does not contain the previously stored value 'Hello => World'!");
    $this->assertTrue($context->has('foo'), "The context does not contain the previously stored value 'foo => bar'!");
    $this->assertAttributeContains('bar', 'contents', $context, "The context contents attribute does not contain the previously stored value 'foo => bar'!");
    $this->assertTrue($context->has('42'), "The context does not contain the previously stored value '42 => false'!");
    $this->assertAttributeContains(false, 'contents', $context, "The context contents attribute does not contain the previously stored value '42 => false'!");

    return $context;
  }

  /**
   * @param Context $context
   * @depends testCanExpand
   * @return Context
   */
  public function testCanGetValues(Context $context) : Context
  {
    $this->assertEquals(27, $context->get('hello-world'), "Previously stored key 'hello-world' differs from definition!");
    $this->assertEquals('World', $context->get('Hello'), "Previously stored key 'World' differs from definition!");
    $this->assertEquals('bar', $context->get('foo'), "Previously stored key 'foo' differs from definition!");
    $this->assertFalse($context->get('42'), "Previously stored key '42' differs from definition!");
    $this->assertEquals('', $context->get('nonexisting'), "Unset key returned unexpected value!");

    return $context;
  }

  /**
   * @return Context
   */
  public function testCanProtectKey() : Context
  {
    $context = new Context();

    $context->protect('foo');

    $this->assertAttributeContains('foo', 'protected', $context, "The context protected attribute does not contain the previously protected key 'foo'!");
    $this->assertFalse($context->push('foo','bar'), "The context illicitly allowed pushing to an protected key!");

    return $context;
  }

  /**
   * @return Context
   */
  public function testCanRegisterProcessor() : Context
  {
    $context = new Context();

    $func = function (string $value) {
      return 'Hello World!';
    };

    $this->assertTrue($context->register('testing', $func), "Could not register a processor on the context!");
    $this->assertTrue($context->hasProcessor('testing'), "The context does not contain the previously registered processor 'testing'!");
    $this->assertAttributeContains($func, 'processors', $context, "The context processors attribute does not contain the previously registered processor 'testing'!");

    $result = $context->getProcessor('testing');
    $this->assertEquals('Hello World!', $result(''), "Previously registered processor 'testing' returned an unexpected value!");

    return $context;
  }

  /**
   * @return Context
   */
  public function testCanRegisterMultipleProcessors() : Context
  {
    $context = new Context();

    $func1 = function (string $value) {
      return 'Hello World!';
    };

    $func2 = function (string $value) {
      return strtoupper($value);
    };

    $this->assertEquals(2, $context->registerAll([
      'testing' => $func1,
      'toupper' => $func2
    ]), "Registering of multiple processors returned an unexpected summary value!");

    $this->assertTrue($context->hasProcessor('testing'), "The context does not contain the previously registered processor 'testing'!");
    $this->assertAttributeContains($func1, 'processors', $context, "The context processors attribute does not contain the previously registered processor 'testing'!");
    $this->assertTrue($context->hasProcessor('toupper'), "The context does not contain the previously registered processor 'toupper'!");
    $this->assertAttributeContains($func2, 'processors', $context, "The context processors attribute does not contain the previously registered processor 'toupper'!");

    $result = $context->getProcessor('testing');
    $this->assertEquals('Hello World!', $result(''), "Previously registered processor 'testing' returned an unexcepted value!");

    $result = $context->getProcessor('toupper');
    $this->assertEquals('HELLO WORLD!', $result('Hello World!'), "Previously registered processor 'toupper' returned an unexcepted value!");

    return $context;
  }

  /**
   * @param Context $context
   * @depends testCanRegisterProcessor
   * @return Context
   */
  public function testCanNotOverrideExistingProcessor(Context $context) : Context
  {
    $func = function (string $value) {
      return 'Bye World!';
    };

    $this->assertTrue($context->hasProcessor('testing'), "The recieved context instance is invalid!");
    $this->assertFalse($context->register('testing', $func), "The context illicitly allowed overriding of an existing processor!");

    return $context;
  }

  /**
   * @param Context $context
   * @depends testCanNotOverrideExistingProcessor
   * @return Context
   */
  public function testCanForceOverrideExistingProcessor(Context $context) : Context
  {
    $func = function (string $value) {
      return 'Bye World!';
    };

    $this->assertTrue($context->hasProcessor('testing'), "The recieved context instance is invalid!");
    $this->assertTrue($context->register('testing', $func, true), "Could not override the processor 'testing' on the context!");
    $this->assertTrue($context->hasProcessor('testing'), "The context does not contain the previously overridden processor 'testing'!");
    $this->assertAttributeContains($func, 'processors', $context, "The context processors attribute does not contain the previously overridden processor 'testing'!");

    $result = $context->getProcessor('testing');
    $this->assertEquals('Bye World!', $result(''), "Previously overridden processor 'testing' returned an unexpected value!");

    return $context;
  }
}

?>
