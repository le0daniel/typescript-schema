<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition;

use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypescriptSchema\Contracts\Validator;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\InternalTransformers;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

class BaseTypeTest extends TestCase
{
    public function testReturnsInvalidOnThrowInValidateAndParseType()
    {
        $mock = new class extends BaseType {
            protected function validateAndParseType(mixed $value, Context $context): mixed
            {
                throw new RuntimeException('Something');
            }

            public function toDefinition(): Definition
            {
                return Definition::same('');
            }
        };

        $result = $mock->execute(null, $context = new Context());
        self::assertEquals(Value::INVALID, $result);
        self::assertCount(1, $context->getIssues());
        self::assertEquals('Something', $context->getIssues()[0]->getPrevious()->getMessage());
    }

    public function testReturnsInvalidOnInvalidResultOnParsing()
    {
        $mock = new class extends BaseType {
            protected function validateAndParseType(mixed $value, Context $context): mixed
            {
                return Value::INVALID;
            }

            public function toDefinition(): Definition
            {
                return Definition::same( '');
            }
        };

        $result = $mock->execute(null, $context = new Context());
        self::assertEquals(Value::INVALID, $result);
        self::assertCount(0, $context->getIssues());
    }

    private function openMock()
    {
        return new class extends BaseType {
            use InternalTransformers;
            protected function validateAndParseType(mixed $value, Context $context): mixed
            {
                return $value;
            }

            public function validate(Closure|Validator $validator): static
            {
                return $this->addValidator($validator);
            }

            public function addTestTransform(Closure $closure): static
            {
                return $this->addInternalTransformer($closure);
            }

            public function toDefinition(): Definition
            {
                return Definition::same('');
            }
        };
    }

    public function testCapturesIssueFromMultipleValidators()
    {
        $result = $this->openMock()
            ->validate(fn() => throw Issue::custom('first'))
            ->validate(fn() => throw Issue::custom('second'))
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertCount(2, $context->getIssues());
    }

    public function testCapturesFatalIssueAndStops()
    {
        $result = $this->openMock()
            ->validate(fn() => throw Issue::custom('first')->fatal())
            ->validate(fn() => throw Issue::custom('second'))
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertCount(1, $context->getIssues());
    }

    public function testRunsRefinersOnlyOnSuccessfulValidation()
    {
        $signal = new \stdClass();
        $signal->status = false;

        $result = $this->openMock()
            ->validate(fn() => throw Issue::custom('first'))
            ->validate(fn() => throw Issue::custom('second'))
            ->refine(function($value) use ($signal) {
                $signal->status = true;
                return $value;
            })
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertFalse($signal->status);
    }

    public function testCaptureMultipleRefiners()
    {
        $result = $this->openMock()
            ->refine(function() {
                throw Issue::custom('');
            })
            ->refine(function() {
                throw Issue::custom('');
            })
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertCount(2, $context->getIssues());
    }

    public function testRunTransformersIfEverythingPassed()
    {
        $result = $this->openMock()
            ->validate(fn() => true)
            ->addTestTransform(fn() => 'This is the result')
            ->refine(fn() => true)
            ->execute(null, $context = new Context());

        self::assertEquals('This is the result', $result);
    }

    public function testCaptureErrorOfTransformers()
    {
        $result = $this->openMock()
            ->addTestTransform(fn() => throw new RuntimeException('Something'))
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertCount(1, $context->getIssues());
        self::assertEquals('Something', $context->getIssues()[0]->getPrevious()->getMessage());
    }

}
