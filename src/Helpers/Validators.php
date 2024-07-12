<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use Closure;
use TypescriptSchema\Contracts\Validator;
use TypescriptSchema\Exceptions\Issue;

trait Validators
{
    /** @var array<Validator>  */
    protected array $validators = [];

    /** @var array */
    private array $refiners = [];

    /**
     * Use the message parameter to customize the error message.
     * - string => Issue::custom(string)
     * - null => Issue::generic()
     * - Closure => Closure(mixed $invalidValue): Issue
     *
     * Example:
     *
     *     $this->addValidator(fn(string $input): bool => strlen($input) > 5) // => Issue::generic()
     *
     *     $this->addValidator(fn(string $input): bool => strlen($input) > 5, 'String must be longer than 5.')
     *
     *     $this->addValidator(fn(string $input): bool => strlen($input) > 5, fn(string $invalidString) => Issue::custom(
     *          message: "String must be longer than 5, got: ${$invalidString}",
     *          data: ['code' => ErrorCode::INVALID_STRING_LENGTH, 'expected' => 5, 'got' => strlen($invalidString)]
     *     ));
     *
     * @param Closure(mixed):bool|Validator $validator
     * @param string|Closure(mixed): Issue|null $message
     * @return $this
     */
    protected function addValidator(Closure|Validator $validator, string|Closure|null $message = null): static
    {
        $instance = clone $this;
        $instance->validators[] = $validator instanceof Closure ? new ClosureValidator($validator, $message) : $validator;
        return $instance;
    }

    /**
     * @param Closure(mixed):bool $refine
     * @param string|Closure(mixed): Issue|null $message
     * @return $this
     */
    public function refine(Closure $refine, string|Closure|null $message = null): static
    {
        $instance = clone $this;
        $instance->refiners[] = new ClosureValidator($refine, $message);
        return $instance;
    }

}
