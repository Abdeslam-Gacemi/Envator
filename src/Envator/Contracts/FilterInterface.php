<?php

namespace Abdeslam\Envator\Contracts;

interface FilterInterface
{
    /**
     * Filters the key and the value and returns them as an array
     *
     * @param array $oldItems the old items parsed
     * @param string $key
     * @param mixed $value
     * @return array the filters key and value as an array ['key' => ..., 'value' => ...]
     */
    public static function filter(array $oldItems, string $key, $value): array;
}
