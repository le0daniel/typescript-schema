# Typescript Schema

This is a simple library with no outside dependencies that generates a typescript schema out of your defined schema.
This can help creating tight contracts in rest APIs for your input and output, without the need and complexity of using
GraphQL or similar.

## Installation

```composer require le0daniel/typescript-schema```

## Getting started

Define your schema for input or output.

```php
use TypescriptSchema\Definition\Schema;

$userType = Schema::object([
    'name' => Schema::string()->nonEmpty()->minLength(1)->maxLength(255),
    'email' => Schema::string()->email()->endsWith('.com')
]);

$userType->toDefinition()->input() // JsonSchema => ['type' => 'object', 'properties' => ['name' => ['type' => 'string'], 'email' => ['type' => 'string']], (...)]
$userType->toDefinition()->output() // JsonSchema => ['type' => 'object', 'properties' => ['name' => ['type' => 'string'], 'email' => ['type' => 'string']], (...)]

$schema = Schema::make($userType);
$schema->parse(['name' => 'The name', 'email' => 'email@test.com', 'other']);
// => Result<['name' => 'The name', 'email' => 'email@test.com']>

$schema->parse(['name' => 'The name', 'email' => 'INVALID', 'other']);
// => Result<Value::INVALID>

$schema->parseOrFail(['name' => 'The name', 'email' => 'INVALID', 'other']);
// => throws ParsingException

$schema->parseOrFail(['name' => 'The name', 'email' => 'email@test.com', 'other']);
// => ['name' => 'The name', 'email' => 'email@test.com']

```

## Difference between parse and serialize

The main difference is that parsing will cast some values to PHP types, whereas serialize will return a format that is JSON serializable. 
Use parse if you have unknown input that should be used in a PHP, use serialize if the result should be Json Serialized. 

Parsing: Takes input and returns PHP Types where possible (Enums, DateTimeImmutable)
Serializing: Takes input and serializes it to JsonSerializable format. Example (Enum => Enum->name, DateTimeImmutable => DateTimeImmutable->format(...))

## Typescript Support

By default, the toDefinition() method generates Json Schema output. You can use it to create a valid typescript declaration using the Typescript utility.

```php
use TypescriptSchema\Utils\Typescript;
use TypescriptSchema\Definition\Schema;

$schema = Schema::make(
    Schema::object([
        'id' => Schema::int()->min(1),
        'name' => Schema::string(),
    ])
);

Typescript::fromJsonSchema($schema->toDefinition()->output());
// => {id:number;name:string}
```

## Primitive types

- String   `Schema::string() | StringType::make()`
- Int      `Schema::float() | FloatType::make()`
- Float    `Schema::float() | FloatType::make()`
- Boolean  `Schema::boolean() | BoolType::make()`
- Literal  `Schema::literal('book') | LiteralType::make('book')`
- DateTime `Schema::dateTime(format) | DateTimeType::make(format)`
- Any      `Schema::any() | AnyType::make()`
- Enum     `Schema::enum(class) | EnumType::make(class)`

### Coercion of values

By default, type checks are strict. Meaning passing a number to a String type will result in a failure.
You can use coercion to make it less strict as following: `Schema::string()->coerce()`. This is available for all
primitive types and tries to parse the value from any input.

This works as following for the different primitives:

- String: `(string) $value`
- Int: `(int) $value`
- Float: `(float) $value`
- Boolean: `Accepts: 0, 1, '0', '1', 'true', 'false'`

### String

The string type supports following default validations:

- min (min amount of characters, including) `Schema::string()->min(5)`
- max (max amount of characters, including) `Schema::string()->max(5)`
- endsWith `Schema::string()->endsWith('.test')`
- startsWith `Schema::string()->startsWith('Hello: ')`
- notEmpty `Schema::string()->notEmpty()`
- regex `Schema::string()->regex('/[a-z]+/')`
- alphaNumeric `Schema::string()->alphaNumeric()`
- email `Schema::string()->email()`

### Int

The int type supports following validations:

- min `Schema::int()->min(5, including: true)`
- max `Schema::int()->max(5, including: true)`

### DateTime

The DateTime primitive accepts by default a string (in given format, defaults to `DateTime::ATOM`) or an instance of
the `DateTimeInterface`. All instances are cast into a DateTimeImmutable.

By default, the output type is as following: `{date: string, timezone_type: number, timezone: string}`. This is the
default JsonSerialization of a DateTime class in PHP.

For output, it is useful to use `Schema::dateTime()->asFormattedString()`, which will return the formatted string
instead of the DateTimeImmutableInstance.

### Enum

An Enum type expects the input to be the Enum instance or a string with the name of the enum. In serialization mode,
the Enum name is outputted (`Enum::CASE->name`).

```php
use TypescriptSchema\Definition\Schema;

enum MyEnum {
    case SUCCESS;
    case FAILURE;
}

$type = Schema::enum(MyEnum::class);

$type->toDefinition()->input()                // => 'SUCCESS'|'FAILURE'
$type->toDefinition()->output()               // => 'SUCCESS'|'FAILURE'

// Parsing always returns the Enum case itself.
Schema::make($type)->parse(MyEnum::SUCCESS) // => MyEnum::SUCCESS
Schema::make($type)->parse('SUCCESS') // => MyEnum::SUCCESS

// Parsing as strings always returns the string with the enum name.
Schema::make($type)->serialize('SUCCESS') // => 'SUCCESS'
Schema::make($type)->serialize(MyEnum::SUCCESS) // => 'SUCCESS'
```

### Any

Both the unknown and any type pass all data through in the form it was before.

```php
use TypescriptSchema\Definition\Schema;

$type = Schema::make(Schema::any());
$type->parse(null) // => Result<null>
$type->parse('string') // => Result<'string'>
$type->parse((object) []) // => Result<object{}>
$type->parse([]) // => Result<array{}>
```

---

## Complex Types

Complex types build up on the primitives and add functionality around them. Following complex types are supported:

- Object
- Array
- Record
- Tuple
- Union / DiscriminatedUnion

### Object

Represents an Object in json with key value pairs that are known and typed. Objects are defined by an associative array
in PHP, where the key needs to be a string and the value a Type or a Field.

```php
use TypescriptSchema\Definition\Schema;
use \TypescriptSchema\Utils\Typescript;

$user = Schema::object([
    'name' => Schema::string(),
]);

Typescript::fromJsonSchema($user->toDefinition()->input())  // => {name: string;}
Typescript::fromJsonSchema($user->toDefinition()->output()) // => {name: string;}
```

By default, resolving a value to a field is done by checking if the key exists in the array or a property of an object.

In some cases, you might want to customize this resolution (Ex: you have a computed output field).

```php
use TypescriptSchema\Definition\Complex\Field;use TypescriptSchema\Definition\Schema;
use TypescriptSchema\Definition\Schema;

$user = Schema::object([
    'fullName' => Field::ofType(Schema::string())
        ->resolvedBy(fn(User $user): string => $user->fullName()),
]);
```

Using fields adds more benefits. You can describe the field or even deprecate it. This is especially useful for output types that serialize your Data.

```php
use TypescriptSchema\Definition\Complex\Field;
use TypescriptSchema\Definition\Schema;

$user = Schema::object([
    'fullName' => Field::ofType(Schema::string())
        ->resolvedBy(fn(User $user): string => $user->fullName())
        ->describe('The combination of title, first and last name')
        ->deprecated('Use lastName, firstName and title to compute manually', DateTime::createFromFormat('Y-m-d', '2024-05-25')),
]);
```

## Extending

You can define your own custom types, by implementing the Type interface. Two methods need to be implemented:

- `public function toDefinition(): SchemaDefinition;`
- `public function parse(mixed $value, Context $context): mixed;`

The definition method should define the Json Schema for input and output, whereas the parse function should parse the given value into the correct type. It MUST NOT throw any exception.
In case of failure, `Value::INVALID` should be returned and all issues added to the context.

If your type needs to serialize values in a specific way you have to additionally implement the `SerializesOutputValue` interface.

Remember, the types should be immutable.

Following utilities (traits) are available for types to use:

- Coerce: Enables `->coerce()`
- HasDefaultValue: `->defaultValue()`
- Nullable: Enables `->nullable()`
- Refinable: Enables `->refine()`
- Transformable: Enables `->transform()`
- Validators: Enables Closure Validators to be used easily.

Example

```php
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Exceptions\Issue;

final class MyCustomType implements Type {
    use Nullable, Refinable, Transformable, Validators;

    public function toDefinition(): SchemaDefinition {
        return new \TypescriptSchema\Data\Schema\Definition(
            ['type' => 'string'],
            ['type' => 'string'],
        );
    }

    public function serializeValue(mixed $value, Context $context): Value|string {
        if (!is_string($value)) {
            $context->addIssue(Issue::custom('Value must be a string.'));
            return Value::INVALID;
        }
        
        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }
        
        return $value;
    }
    
    public function minLength(): MyCustomType {
        return $this->addValidator(function (string $value) {
            return strlen($value) > 5;
        });
    }
}

```