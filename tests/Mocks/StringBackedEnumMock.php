<?php declare(strict_types=1);

namespace Tests\Mocks;

enum StringBackedEnumMock: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
}
