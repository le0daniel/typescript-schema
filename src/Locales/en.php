<?php declare(strict_types=1);

return [
    'failed' => 'Invalid data',
    'coercion_failure' => 'Value needs to be of type :expected',
    'invalid_type' => 'Value needs to be of type :expected',
    'invalid_key' => 'Value is required',
    'generic_failure' => 'Invalid value',
    'internal_error' => 'Invalid value',
    'string.invalid_regex' => 'Value did not match expected pattern',
    'string.invalid_email' => 'Value is not a valid email address',
    'string.invalid_min' => 'Value must be at least :min characters',
    'string.invalid_empty' => 'Value cannot be empty',
    'string.invalid_alphanumeric' => 'Value must be alphanumeric',
    'string.invalid_max' => 'Value must be less than :max characters',
    'string.invalid_ends_with' => 'Value must end with :endsWith',
    'string.invalid_starts_with' => 'Value must start with :startsWith',
    'int.invalid_min.excluding' => 'The value must be bigger than :min',
    'int.invalid_min.including' => 'The value must be bigger or equal to :min',
    'int.invalid_max.excluding' => 'The value must be smaller than :max',
    'int.invalid_max.including' => 'The value must be smaller or equal to :max',
    'datetime.invalid_before' => 'Value must be before :before',
    'datetime.invalid_after' => 'Value must be after :after',
];