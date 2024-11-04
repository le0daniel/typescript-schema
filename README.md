# Typescript Schema

This is a simple library with no outside dependencies that generates a typescript schema out of your defined schema.
This can help creating tight contracts in rest APIs for your input and output, without the need and complexity of using
GraphQL or similar.

This library is heavily inspired by GraphQL and Zod. It first parses, then validates and transforms it.

## Installation

```composer require le0daniel/typescript-schema```

## Getting started

Define your schema for input or output.

```php
use TypescriptSchema\Schema;

$user = Schema::object([
    'name' => Schema::string()->nonEmpty()->minLength(1)->maxLength(255),
    'email' => Schema::string()->email()->endsWith('.com')
]);

$user->toDefinition()->input // {name: string, email: string}
$user->toDefinition()->output // {name: string, email: string}

$user->parse(['name' => 'The name', 'email' => 'email@test.com', 'other']) // ['name' => 'The name', 'email' => 'email@test.com']
$result = $user->safeParse([/* unsafe input */])

$result->isSuccess();
$result->getData(); // Returns the data on success or null on failure.
```

## Primitive types

- String   `Schema::string() | StringType::make()`
- Int      `Schema::float() | FloatType::make()`
- Float    `Schema::float() | FloatType::make()`
- Boolean  `Schema::boolean() | BoolType::make()`
- Literal  `Schema::literal('book') | LiteralType::make('book')`
- DateTime `Schema::dateTime(format) | DateTimeType::make(format)`
- Unknown  `Schema::unknown() | UnknownType::make()`
- Any      `Schema::any() | AnyType::make()`
- Enum     `Schema::enum(class) | EnumType::make(class)`

### Coercion of values

By default, type checks are strict. Meaning passing a number to a String type will result in a failure.
You can use coercion to make it less strict as following: `Schema::string()->coerce()`. This is available for all
primitive types.

This works as following for the different primitives:

- String: `(string) $value`
- Int: `(int) $value`
- Float: `(float) $value`
- Boolean: `Accepts: 0, 1, '0', '1', 'true', 'false'`
- Enum: Accepts the enum value for Backed enums.
- DateTime: No Change in behaviour
- Literal: No Change in behaviour.
- Any: No Change in behaviour.
- Unknown: No Change in behaviour.

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

Following Transformers are available:

- trim `Schema::string()->trim()`
- lowerCase `Schema::string()->lowerCase()`
- upperCase `Schema::string()->upperCase()`

### Int

The int type supports following validations:

- min (including) `Schema::int()->min(5)`
- max (including) `Schema::int()->max(5)`

### DateTime

The DateTime primitive accepts by default a string (in given format, defaults to `DateTime::ATOM`) or an instance of
the `DateTimeInterface`. All instances are cast into a DateTimeImmutable.

By default, the output type is as following: `{date: string, timezone_type: number, timezone: string}`. This is the
default JsonSerialization of a DateTime class in PHP.

For output, it is useful to use `Schema::dateTime()->asFormattedString()`, which will return the formatted string
instead of the DateTimeImmutableInstance.

### Enum

An Enum type expects the input to be the Enum instance or a string with the name of the enum.

```php
use TypescriptSchema\Schema;
use TypescriptSchema\Exceptions\ParsingException;

enum MyEnum {
    case SUCCESS;
    case FAILURE;
}

$type = Schema::enum(MyEnum::class);

$type->toDefinition()->input                // => 'SUCCESS'|'FAILURE'
$type->toDefinition()->output               // => never
$type->asString()->toDefinition()->output   // => 'SUCCESS'|'FAILURE'

// Parsing always returns the Enum case itself.
$type->parse(MyEnum::SUCCESS) // => MyEnum::SUCCESS
$type->parse('SUCCESS') // => MyEnum::SUCCESS

// Parsing as strings always returns the string with the enum name.
$type->asString()->parse('SUCCESS') // => 'SUCCESS'
$type->asString()->parse(MyEnum::SUCCESS) // => 'SUCCESS'
```

As UnitEnums in PHP can not be Json serialized, the default output type for unbacked enums is `never`.
When using enums, consider using the `asString()` transformer to have consistent output for json responses.

**Behaviour for Backed Enums**

Backed enums can be serialized to Json, so the output type is eiter `literals<number>|literals<string>`

Example

```php
use TypescriptSchema\Schema;

enum MyEnum: string {
    case SUCCESS = 'success';
    case ERROR = 'error';
}

$type = Schema::enum(MyEnum::class);

$type->toDefinition()->input                // => 'SUCCESS'|'ERROR'
$type->toDefinition()->output               // => 'success'|'error'
$type->asString()->toDefinition()->output   // => 'SUCCESS'|'ERROR'

// Parsing always returns the Enum case itself.
$type->parse(MyEnum::SUCCESS) // => MyEnum::SUCCESS
$type->parse('SUCCESS') // => MyEnum::SUCCESS

// Parsing as strings always returns the string with the enum name.
$type->asString()->parse('SUCCESS') // => 'SUCCESS'
$type->asString()->parse(MyEnum::SUCCESS) // => 'SUCCESS'

// Coercion works with the values
$type->parse('success') // @throws
$type->coerce()->parse('success') // => MyEnum::SUCCESS

```

### Unknown / Any

Both the unknown and any type pass all data through in the form it was before.

```php
use TypescriptSchema\Schema;

$type = Schema::any();
$type->parse(null) // => null
$type->parse('string') // => 'string'
$type->parse((object) []) // => object{}
$type->parse([]) // => array
// (...)
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
use TypescriptSchema\Schema;

$user = Schema::object([
    'name' => Schema::string(),
]);

$user->toDefinition()->input  // => {name: string;}
$user->toDefinition()->output // => {name: string;}
```

By default, resolving a value to a field is done by checking if the key exists in the array or a property of an object.

In some cases, you might want to customize this resolution (Ex: you have a computed output field).

```php
use TypescriptSchema\Schema;
use TypescriptSchema\Definition\Complex\Field;

$user = Schema::object([
    'fullName' => Field::ofType(Schema::string())
        ->resolvedBy(fn(User $user): string => $user->fullName()),
]);
```

Using fields adds more benefits. You can describe the field or even deprecate it. This is especially useful for output types that serialize your Data.
```php
use TypescriptSchema\Schema;
use TypescriptSchema\Definition\Complex\Field;

$user = Schema::object([
    'fullName' => Field::ofType(Schema::string())
        ->resolvedBy(fn(User $user): string => $user->fullName())
        ->describe('The combination of title, first and last name')
        ->deprecated('Use lastName, firstName and title to compute manually', DateTime::createFromFormat('Y-m-d', '2024-05-25')),
]);
```