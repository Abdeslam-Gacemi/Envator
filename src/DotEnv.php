<?php

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\Contracts\ParserInterface;
use Abdeslam\DotEnv\Contracts\ResolverInterface;
use InvalidArgumentException;

class DotEnv
{
    /** @var ResolverInterface */
    protected $resolver;
    
    /** @var ParserInterface */
    protected $parser;
    
    /** @var string[] */
    protected $loadedFiles = [];

    /** @var array */
    protected $items = [];

    /** @var string[] */
    protected $filters = [];

    /** @var array */
    protected $options = [];

    const NO_DEFAULT_VALUE = '__no__default__value__';

    public function __construct(?ResolverInterface $resolver = null, ?ParserInterface $parser = null) {
        $this->resolver = $resolver ?: new Resolver();
        $this->parser = $parser ?: new Parser();
    }

    public function addFilter(string $filter): DotEnv
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function hasFilter(string $filter): bool
    {
        return in_array($filter, $this->filters);
    }

    public function load(string ...$filepaths): DotEnv
    {
        $this->resolver->resolve($filepaths);
        foreach ($filepaths as $filepath) {
            if (in_array($filepath, $this->loadedFiles)) {
                continue;
            }
            $resource = fopen($filepath, 'r');
            $parsedItems = $this->parser->setResource($resource)->parse($this->all(), $this->filters); 
            $this->items = array_merge($this->all(), $parsedItems);
            $this->loadedFiles[] = $filepath;
        }
        return $this;
    }

    public function getLoadedFiles(): array
    {
        return $this->loadedFiles;
    }

    public function get(string $key, $default = self::NO_DEFAULT_VALUE)
    {
        if (!$this->has($key)) {
            if ($default == self::NO_DEFAULT_VALUE) {
                throw new InvalidArgumentException("Item with the key $key was not found");
            } else {
                return $default;
            }
        }
        return $this->items[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function reset(): DotEnv
    {
        $this->loadedFiles = [];
        $this->items = [];
        $this->filters = [];
        $this->options = [];
        return $this;
    }

    public function populate(array $options = [])
    {
        // code
    }

}
