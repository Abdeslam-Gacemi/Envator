<?php

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\Contracts\ParserInterface;
use Abdeslam\DotEnv\Contracts\ResolverInterface;
use Abdeslam\DotEnv\Contracts\CacheManagerInterface;
use Abdeslam\DotEnv\Exceptions\ItemNotFoundException;
use Abdeslam\DotEnv\Exceptions\InvalidOptionException;

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

    /** @var CacheManagerInterface */
    protected $cacheManager;

    /** @var array */
    protected $options = [
        self::GLOBAL_ENV => true,
        self::PUT_ENV => true,
        self::APACHE => false,
        self::SERVER => true
    ];

    const NO_DEFAULT_VALUE = '__no__default__value__';
    const GLOBAL_ENV = 0;
    const PUT_ENV = 1;
    const APACHE = 2;
    const SERVER = 3;

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
        $this->resolver->setFilepaths($filepaths)->resolve();
        foreach ($filepaths as $filepath) {
            if (in_array($filepath, $this->loadedFiles)) {
                continue;
            }
            $cacheManager = $this->getCacheManager();
            if ($cacheManager && $cacheManager->has($filepath)) {
                $this->items = array_merge($this->items, $cacheManager->get($filepath));
                $this->loadedFiles[] = $filepath;
                continue;
            }
            $resource = fopen($filepath, 'r');
            $parsedItems = $this->parser->setResource($resource)->parse($this->all(), $this->filters); 
            fclose($resource);
            $this->items = array_merge($this->all(), $parsedItems);
            $this->loadedFiles[] = $filepath;
            if ($cacheManager) {
                $cacheManager->set($filepath, $parsedItems);
            }
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
                throw new ItemNotFoundException("Item with the key $key was not found");
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

    public function setCacheManager(CacheManagerInterface $cacheManager): DotEnv
    {
        $this->cacheManager = $cacheManager;
        return $this;
    }

    public function getCacheManager(): ?CacheManagerInterface
    {
        return $this->cacheManager;
    }

    public function populate(array $options = [])
    {
        $options = array_merge($this->options, $options);

        foreach ($this->all() as $key => $value) {
           if ($options[self::GLOBAL_ENV] === true) {
                $_ENV[$key] = $value;
           }
           if ($options[self::PUT_ENV] === true) {
                putenv("$key=$value");
            }
            if ($options[self::APACHE] === true) {
                if (function_exists('apache_setenv')) {
                    apache_setenv($key, $value);
                } else {
                    throw new InvalidOptionException("Function apache_setenv() does not exist, unable to populate to apache environment variables");
                }
            }
            if ($options[self::SERVER] === true) {
                $_SERVER[$key] = $value;
            }
        }
    }

}
