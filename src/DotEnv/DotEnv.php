<?php
/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv;

use Psr\SimpleCache\CacheInterface;
use Abdeslam\DotEnv\Contracts\ParserInterface;
use Abdeslam\DotEnv\Contracts\ResolverInterface;
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

    /** @var string[] FQCN of implementations of FilterInterface::class */
    protected $filters = [];

    /** @var CacheInterface */
    protected $cacheManager;

    /** @var array */
    protected $options = [
        self::GLOBAL_ENV => true,
        self::PUT_ENV => true,
        self::APACHE => false,
        self::SERVER => true
    ];

    /** @var string */
    const NO_DEFAULT_VALUE = '__no__default__value__';

    /** @var int */
    const GLOBAL_ENV = 0;

    /** @var int */
    const PUT_ENV = 1;

    /** @var int */
    const APACHE = 2;
    
    /** @var int */
    const SERVER = 3;

    /**
     * @param ResolverInterface|null $resolver
     * @param ParserInterface|null $parser
     */
    public function __construct(?ResolverInterface $resolver = null, ?ParserInterface $parser = null)
    {
        $this->resolver = $resolver ?: new Resolver();
        $this->parser = $parser ?: new Parser();
    }

    /**
     * sets the array of filters to a list of FQCNs of implementations of FilterInterface::class
     *
     * @param array $filters
     * @return DotEnv
     */
    public function setFilters(array $filters): DotEnv
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @param string $filter a FQCN of an implementation of FilterInterface::class
     * @return DotEnv
     */
    public function addFilter(string $filter): DotEnv
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * checks if a filter is registered in $filters array
     *
     * @param string $filter a FQCN of an implementation of FilterInterface::class
     * @return boolean
     */
    public function hasFilter(string $filter): bool
    {
        return in_array($filter, $this->filters);
    }

    /**
     * resolves, parses and stores the content of .env files or load them from the cache if the cache is active and the file is cached
     *
     * @param string[] $filepaths file paths of .env files
     * @return DotEnv
     */
    public function load(string ...$filepaths): DotEnv
    {
        $this->resolver->setFilepaths(...$filepaths)->resolve();
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

    /**
     * returns the array of filepaths of loaded .env files
     *
     * @return array
     */
    public function getLoadedFiles(): array
    {
        return $this->loadedFiles;
    }

    /**
     * gets an item loaded from .env files by the key
     *
     * @param string $key
     * @param mixed $default default value to return if the searched key was not found
     * @return mixed
     */
    public function get(string $key, $default = self::NO_DEFAULT_VALUE): mixed
    {
        if (!$this->has($key)) {
            if ($default === self::NO_DEFAULT_VALUE) {
                throw new ItemNotFoundException("Item with the key $key was not found");
            } else {
                return $default;
            }
        }
        return $this->items[$key];
    }

    /**
     * checks if an item in the items loaded from .ev files exits by its key
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * gets the array of all the items loaded from .en files
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * resets the object
     *
     * @return DotEnv
     */
    public function reset(): DotEnv
    {
        $this->loadedFiles = [];
        $this->items = [];
        $this->filters = [];
        $this->options = [
            self::GLOBAL_ENV => true,
            self::PUT_ENV => true,
            self::APACHE => false,
            self::SERVER => true
        ];
        return $this;
    }

    /**
     * activates the cache and sets the CacheManager
     *
     * @param CacheInterface $cacheManager
     * @return DotEnv
     */
    public function setCacheManager(CacheInterface $cacheManager): DotEnv
    {
        $this->cacheManager = $cacheManager;
        return $this;
    }

    /**
     * gets the cache manager instance
     *
     * @return CacheInterface|null
     */
    public function getCacheManager(): ?CacheInterface
    {
        return $this->cacheManager;
    }

    /**
     * populate items loaded from .env files to the environment
     *
     * @param array $options possible options are:
     * - DotEnv::GLOBAL_ENV => bool : populate to the super global variable $_ENV
     * - DotEnv::PUT_ENV    => bool : #1 populate using the function putenv()
     * - DotEnv::APACHE     => bool : populate using the function apache_setenv()
     * - DotEnv::SERVER     => bool : populate to the super global variable $_SERVER
     * @return void
     */
    public function populate(array $options = []): DotEnv
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
        return $this;
    }
}
