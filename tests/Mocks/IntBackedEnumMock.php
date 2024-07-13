<?php declare(strict_types=1);

namespace Tests\Mocks;

enum IntBackedEnumMock: int
{
    case SUCCESS = 0;
    case FAILURE = 1;
}
