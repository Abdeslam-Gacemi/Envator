<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class TrimQuotesFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public static function filter(array $oldItems, string $key, $value): array
    {
        $key = trim($key, '\'"');
        if (is_string($value)) {
            $value = trim($value, '\'"');
        }
        return ['key' => $key, 'value' => $value];
    }
}
