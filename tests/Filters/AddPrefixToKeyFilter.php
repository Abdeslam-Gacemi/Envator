<?php

namespace Tests\Filters;

use Abdeslam\DotEnv\Contracts\FilterInterface;

class AddPrefixToKeyFilter implements FilterInterface
{
    public static function filter(array $oldItems, string $key, $value): array
    {
        $key = "MY_PREFIX_" . $key;
        return ['key' => $key, 'value' => $value];
    }
}
