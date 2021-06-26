<?php

namespace Abdeslam\DotEnv\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class VariableFilter implements FilterInterface
{
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
