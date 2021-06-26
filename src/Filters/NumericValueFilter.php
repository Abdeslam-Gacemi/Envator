<?php

namespace Abdeslam\DotEnv\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class NumericValueFilter implements FilterInterface
{
    public static function filter(array $oldItems, string $key, $value): array
    {
        if (is_string($value) && is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                $value = (float) $value;
            }
            $value = (int) $value;
        }
        return ['key' => $key, 'value' => $value];
    }
}