<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Definition\Complex\DiscriminatedUnionType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Tests\Definition\TestsParsing;

class DiscriminatedUnionTypeTest extends TestCase
{
    use TestsParsing;

    public function testFailureWithOnlyOneType(): void
    {
        $this->expectException(\RuntimeException::class);
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

        self::assertEquals("{type: 'success';}|{type: 'failure';}", $type->toInputDefinition());
        self::assertEquals("{type: 'success';}|{type: 'failure';}", $type->toOutputDefinition());
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
