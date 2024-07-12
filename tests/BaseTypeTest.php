<?php declare(strict_types=1);

namespace Tests;

use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypescriptSchema\BaseType;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Contracts\Validator;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;

class BaseTypeTest extends TestCase
{
    public function testReturnsInvalidOnThrowInValidateAndParseType()
    {
        $mock = new class extends BaseType {
            protected function validateAndParseType(mixed $value, Context $context): mixed
            {
                throw new RuntimeException('Something');
            }

            public function toDefinition(): string
            {
                return '';
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

            public function toDefinition(): string
            {
                return '';
            }
        };

        $result = $mock->execute(null, $context = new Context());
        self::assertEquals(Value::INVALID, $result);
        self::assertCount(0, $context->getIssues());
    }

    private function openMock()
    {
        return new class extends BaseType {
            protected function validateAndParseType(mixed $value, Context $context): mixed
            {
                return $value;
            }

            public function addValidator(Closure|Validator $validator, string|Closure|null $message = null): static
            {
                return parent::addValidator($validator);
            }

            public function addTransformer(Closure $transformer): static
            {
                return parent::addTransformer($transformer);
            }

            public function toDefinition(): string
            {
                return '';
            }
        };
    }

    public function testCapturesIssueFromMultipleValidators()
    {
        $result = $this->openMock()
            ->addValidator(fn() => throw Issue::custom('first'))
            ->addValidator(fn() => throw Issue::custom('second'))
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertCount(2, $context->getIssues());
    }

    public function testCapturesFatalIssueAndStops()
    {
        $result = $this->openMock()
            ->addValidator(fn() => throw Issue::custom('first')->fatal())
            ->addValidator(fn() => throw Issue::custom('second'))
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertCount(1, $context->getIssues());
    }

    public function testRunsRefinersOnlyOnSuccessfulValidation()
    {
        $signal = new \stdClass();
        $signal->status = false;

        $result = $this->openMock()
            ->addValidator(fn() => throw Issue::custom('first'))
            ->addValidator(fn() => throw Issue::custom('second'))
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
            ->addValidator(fn() => true)
            ->refine(fn() => true)
            ->addTransformer(fn() => 'This is the result')
            ->execute(null, $context = new Context());

        self::assertEquals('This is the result', $result);
    }

    public function testCaptureErrorOfTransformers()
    {
        $result = $this->openMock()
            ->addTransformer(fn() => throw new RuntimeException('Something'))
            ->execute(null, $context = new Context());

        self::assertEquals(Value::INVALID, $result);
        self::assertCount(1, $context->getIssues());
        self::assertEquals('Something', $context->getIssues()[0]->getPrevious()->getMessage());
    }

}
