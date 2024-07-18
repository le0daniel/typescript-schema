<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Result;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Exceptions\ParsingException;
use TypescriptSchema\Helpers\Context;

/**
 * @mixin Type
 */
trait ParsesInput
{

    /**
     * Given any input it runs the validation
     * @param mixed $value
     * @return mixed
     * @throws ParsingException
     */
    public function parse(mixed $value): mixed
    {
        $result = $this->safeParse($value);
        if (!$result->isSuccess()) {
            throw $result->toThrowable();
        }
        return $result->getData();
    }

    public function safeParse(mixed $value, bool $allowPartial = false): Result
    {
        try {
            $context = new Context($allowPartial);
            $result = $this->execute($value, $context);
            return new Result($result, $context->getIssues());
        } catch (Throwable $exception) {
            return new Result(Value::INVALID, [Issue::captureThrowable($exception)]);
        }
    }

}