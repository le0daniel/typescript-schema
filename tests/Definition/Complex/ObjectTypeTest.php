<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Data\Enum\IssueType;
use TypescriptSchema\Definition\Complex\Field;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Exceptions\ParsingException;
use TypescriptSchema\Tests\Mocks\GettersMock;

class ObjectTypeTest extends TestCase
{

    public function testImmutability(): void
    {
        $object = ObjectType::make(['name' => StringType::make()]);
        self::assertNotSame($object, $object->passThrough());
    }

    public function testObjectTypeDefinition()
    {
        $type = ObjectType::make([
            'id' => StringType::make(),
            'name' => StringType::make()->nullable(),
            'opt' => Field::ofType(StringType::make()->nullable())->optional(),
        ]);

        self::assertSame('{id: string; name: string|null; opt?: string|null;}', $type->toDefinition()->output);
        self::assertTrue($type->toDefinition()->input === $type->toDefinition()->output);
        self::assertSame('{id: string; name: string|null; opt?: string|null; [key: string]: unknown;}', $type->passThrough()->toDefinition()->output);
        self::assertTrue($type->passThrough()->toDefinition()->input === $type->passThrough()->toDefinition()->output);
    }

    public function testPassThroughAsClosure(): void
    {
        $type = ObjectType::make([
            'id' => StringType::make(),
        ])->passThrough(fn(array $items) => ['key' => 'value', 'id' => 123]);

        self::assertEquals(['id' => '345', 'key' => 'value'], $type->parse(['id' => '345']));

        $justPassThrough = $type->passThrough();
        self::assertEquals(['id' => '345', 'other' => 'passed'], $justPassThrough->parse(['id' => '345', 'other' => 'passed']));
    }

    public function testCorrectFailureOnNull(): void
    {
        $object = ObjectType::make(['name' => StringType::make()]);
        $result = $object->safeParse(null);
        self::assertFalse($result->isSuccess());
        self::assertEquals(IssueType::INVALID_TYPE, $result->issues[0]->type);
    }

    public function testOnlyOutputDefinition(): void
    {
        $type = ObjectType::make([
            'id' => StringType::make(),
            'name' => Field::ofType(StringType::make())->onlyOutput(),
        ]);
        self::assertSame('{id: string; }', $type->toDefinition()->input);
        self::assertSame('{id: string; name: string;}', $type->toDefinition()->output);
    }

    public function testTypeDefinitionWithDocBlock(): void
    {
        $type = ObjectType::make([
            'id' => Field::ofType(StringType::make())->describe('this is the description')->deprecated(),
            'name' => StringType::make(),
        ]);
        $expectedDescription = <<<DOC
{/**
 * this is the description
 * 
 * @deprecated
 */id: string; name: string;}
DOC;
        self::assertEquals($expectedDescription, $type->toDefinition()->input);
        self::assertEquals($expectedDescription, $type->toDefinition()->output);
    }

    public function testParsing(): void
    {
        $type = ObjectType::make([
            'id' => IntType::make()->min(1),
            'name' => StringType::make()->min(1)->max(10)->nullable(),
            'opt' => Field::ofType(StringType::make()->nullable())->optional(),
        ]);

        self::assertSame(['id' => 1, 'name' => 'my-name'], $type->parse(['id' => 1, 'name' => 'my-name', 'other' => true]));

        self::assertSame(['id' => 123, 'name' => 'my-other'], $type->parse(GettersMock::standardObject([
            'id' => 123,
            'name' => 'my-other',
            'other' => true,
        ])));
    }

    public function testFieldResolver()
    {
        $type = ObjectType::make([
            'id' => Field::ofType(IntType::make()->min(1))->resolvedBy(fn($data) => $data->id()),
        ]);

        $object = new class () {
            public function id(): int
            {
                return 123456;
            }
        };
        self::assertSame(['id' => 123456], $type->parse($object));
    }

    public function testPassThrough()
    {
        $type = ObjectType::make([
            'id' => Field::ofType(IntType::make()->min(1))
                ->resolvedBy(fn($data) => $data['id'] + 2),
        ]);

        try {
            self::assertEquals(['id' => 125, 'other' => true], $type->passThrough()->parse(['id' => 123, 'other' => true]));
        } catch (ParsingException $exception) {
            var_dump($exception->issues);
        }
    }

    public function testShowsErrorsForAllFieldsOnFailure(): void
    {
        $schema = ObjectType::make([
            'id' => IntType::make()->min(1),
            'password' => StringType::make()->min(10),
        ]);

        $issues = $schema->safeParse(['id' => 0, 'password' => 'test'])->issues;

        self::assertCount(2, $issues);
        self::assertEquals("int.invalid_min", $issues[0]->getLocalizationKey());
        self::assertEquals("string.invalid_min", $issues[1]->getLocalizationKey());
    }

}
