<?php

namespace Abdeslam\DotEnv\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class BooleanValueFilter implements FilterInterface
{
    public static function filter(array $oldItems, string $key, $value): array
    {
        if (is_string($value)) {
            $loweredValue = strtolower($value);
            if ($loweredValue === 'true' || $loweredValue === 'false') {
                $value = $loweredValue === 'true' ? true : false;
            }
        }
        return ['key' => $key, 'value' => $value];
    }
}