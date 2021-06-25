<?php

namespace Abdeslam\DotEnv\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class TrimQuotesFilter implements FilterInterface
{
    public static function filter(array $oldItems, string $key, $value): array
    {
        $key = trim($key, '\'"');
        if (is_string($value)) {
            $value = trim($value, '\'"');
        }
        return ['key' => $key, 'value' => $value];
    }
}