<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use RuntimeException;
use TypescriptSchema\Definition\Complex\DiscriminatedUnionType;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Tests\Definition\TestsParsing;
use TypescriptSchema\Utils\Typescript;

class DiscriminatedUnionTypeTest extends TestCase
{
    use TestsParsing;

    public function testFailureWithOnlyOneType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("A discriminatory union type must have at least two types.");
        DiscriminatedUnionType::make('type', ObjectType::make(['type' => LiteralType::make('success')]));
    }

    public function testDefinition()
    {
        $type = DiscriminatedUnionType::make(
            'type',
            ObjectType::make(['type' => LiteralType::make('success')]),
            ObjectType::make(['type' => LiteralType::make('failure')])
        );

        self::assertEquals("{type:'success'}|{type:'failure'}", Typescript::fromJsonSchema($type->toDefinition()->input()));
        self::assertEquals("{type:'success'}|{type:'failure'}", Typescript::fromJsonSchema($type->toDefinition()->output()));
    }


    public static function parsingDataProvider(): array
    {
        $type = DiscriminatedUnionType::make(
            'type',
            ObjectType::make(['type' => LiteralType::make('success')]),
            ObjectType::make(['type' => LiteralType::make('failure')])
        );

        return [
            'first match' => [
                $type,
                [
                    ['type' => 'success']
                ],
            ],
            'second match' => [
                $type,
                [
                    ['type' => 'failure']
                ],
            ],
            'failure on no match' => [
                $type,
                [],
                [
                    ['type' => 'other']
                ]
            ]
        ];
    }
}
