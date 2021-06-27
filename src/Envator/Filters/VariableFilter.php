<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\Envator\Filters;

use Abdeslam\Envator\Contracts\FilterInterface;

class VariableFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public static function filter(array $oldItems, string $key, $value): array
    {
        if (is_string($value) && preg_match_all('/\${(.+?)}/', $value, $matches, PREG_SPLIT_DELIM_CAPTURE)) {
            foreach ($matches as $variable) {
                if (isset($oldItems[$variable[1]])) {
                    $value = str_replace($variable[0], $oldItems[$variable[1]], $value);
                }
            }
        }
        return ['key' => $key, 'value' => $value];
    }
}
