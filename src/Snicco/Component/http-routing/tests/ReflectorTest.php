<?php

declare(strict_types=1);

namespace Snicco\Component\HttpRouting\Tests;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use Snicco\Component\HttpRouting\Reflector;
use Traversable;

final class ReflectorTest extends TestCase
{
    /**
     * @test
     */
    public function test_is_interface_with_class_string(): void
    {
        Reflector::assertInterfaceString(TestSubject::class, Countable::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf("Expected class-string<%s>\nGot: [%s].", JsonSerializable::class, TestSubject::class)
        );
        Reflector::assertInterfaceString(TestSubject::class, JsonSerializable::class);
    }

    /**
     * @test
     */
    public function test_with_extended_interface(): void
    {
        Reflector::assertInterfaceString(TestTraversable::class, Iterator::class);
        Reflector::assertInterfaceString(TestTraversable::class, Traversable::class);

        $this->expectException(InvalidArgumentException::class);
        Reflector::assertInterfaceString(TestTraversable::class, ArrayAccess::class);
    }

    /**
     * @test
     */
    public function test_false_for_missing_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Reflector::assertInterfaceString('Foo', ArrayAccess::class);
    }

    /**
     * @test
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress UndefinedClass
     */
    public function test_exception_for_bad_interface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Interface [Foo] does not exist.');

        Reflector::assertInterfaceString(TestTraversable::class, 'Foo');
    }

    /**
     * @test
     * @psalm-suppress UnusedClosureParam
     */
    public function test_firstParameterType_with_closure(): void
    {
        $this->assertSame(
            'string',
            Reflector::firstParameterType(function (string $foo): void {
                //
            })
        );
    }

    /**
     * @test
     */
    public function test_firstParameterType_with_class_string_uses_constructor(): void
    {
        $this->assertSame('string', Reflector::firstParameterType(ClassWithConstructor::class));
    }
}

class NoConstructor
{
}

class ClassWithConstructor
{
    public function __construct(string $foo)
    {
    }

    public function someMethod(string $foo, string $bar): void
    {
    }
}

class TestSubject implements Countable
{
    public function count(): int
    {
        return 0;
    }
}

class TestTraversable implements Iterator
{
    #[ReturnTypeWillChange]
    
    public function current()
    {
    }

    public function next(): void
    {
    }

    #[ReturnTypeWillChange]
    
    public function key()
    {
    }

    public function valid(): bool
    {
        return true;
    }

    public function rewind(): void
    {
        //
    }
}
