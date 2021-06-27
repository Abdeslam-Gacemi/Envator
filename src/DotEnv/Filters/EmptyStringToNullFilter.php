<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class EmptyStringToNullFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public static function filter(array $oldItems, string $key, $value): array
    {
        if (is_string($value) && $value === '') {
            $value = null;
        }
        return ['key' => $key, 'value' => $value];
    }
}
