<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class NumericValueFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public static function filter(array $oldItems, string $key, $value): array
    {
        if (is_string($value) && is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                $value = (float) $value;
            } else {
                $value = (int) $value;
            }
        }
        return ['key' => $key, 'value' => $value];
    }
}
