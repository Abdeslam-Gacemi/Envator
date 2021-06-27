<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\Envator\Filters;

use Abdeslam\Envator\Contracts\FilterInterface;

class BooleanValueFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
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
