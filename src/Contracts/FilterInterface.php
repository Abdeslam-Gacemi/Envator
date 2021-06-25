<?php

namespace Abdeslam\DotEnv\Contracts;

interface FilterInterface
{
    public static function filter(array $oldItems, string $key, $value): array;
}