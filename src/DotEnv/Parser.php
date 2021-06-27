<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv;

use ReflectionClass;
use Abdeslam\DotEnv\Contracts\FilterInterface;
use Abdeslam\DotEnv\Contracts\ParserInterface;
use Abdeslam\DotEnv\Exceptions\InvalidFilterException;
use Abdeslam\DotEnv\Exceptions\InvalidEnvFileException;
use Abdeslam\DotEnv\Exceptions\InvalidResourceException;
use Abdeslam\DotEnv\Exceptions\InvalidFilterReturnValueException;

class Parser implements ParserInterface
{
    /** @var resource|null */
    protected $resource;

    /**
     * @inheritDoc
     */
    public function setResource($resource): ParserInterface
    {
        /** @var resource $resource */
        if (!is_resource($resource)) {
            throw new InvalidEnvFileException("File to parse must be a valid readable resource");
        }
        $this->resource = $resource;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @inheritDoc
     */
    public function parse(array $oldItems, array $filters = []): array
    {
        if (!$this->resource) {
            throw new InvalidResourceException("No resource to parse");
        }
        $items = [];
        while (!feof($this->resource)) {
            $line = trim(fgets($this->resource));
            if (strpos($line, '#') === 0 || strpos($line, '=') === 0 || $line === '') {
                continue;
            }
            $keyValue = explode('=', $line);
            $key = trim($keyValue[0]);
            $value = isset($keyValue[1]) ? trim($keyValue[1]) : null;
            foreach ($filters as $filter) {
                $this->validateFilter($filter);
                $oldItems = array_merge($oldItems, $items);
                /** @var FilterInterface $filter */
                $filteredData = $filter::filter($oldItems, $key, $value);
                $this->validateFilteredData($filteredData);
                $key = $filteredData['key'];
                $value = $filteredData['value'];
            }
            $items[$key] = $value;
        }
        return $items;
    }

    /**
     * check if a FQCN is a valid implementation of FilterInterface::class
     *
     * @param string $filter
     * @return void
     */
    protected function validateFilter(string $filter)
    {
        if (!class_exists($filter)) {
            throw new InvalidFilterException("Filter class $filter not found");
        }
        $reflect = new ReflectionClass($filter);
        $filterInterface = FilterInterface::class;
        if (!$reflect->implementsInterface(FilterInterface::class)) {
            throw new InvalidFilterException("Filter class must implement $filterInterface");
        }
    }

    /**
     * checks if the returned data of the filter is a valid array
     *
     * in the format ['key' => ..., 'value' => ...]
     *
     * @param array $filteredData
     * @return void
     * @throws InvalidFilterReturnValueException
     */
    protected function validateFilteredData(array $filteredData)
    {
        if (!key_exists('key', $filteredData) || !key_exists('value', $filteredData)) {
            throw new InvalidFilterReturnValueException("Filter must return an array in the format ['key' => '...', 'value' => '...']");
        }
    }
}
