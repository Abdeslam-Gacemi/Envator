<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\Contracts\InvalidCacheItemException;
use Abdeslam\DotEnv\Exceptions\InvalidCacheDirectoryException;
use Psr\SimpleCache\CacheInterface;
use SplFileInfo;

class CacheManager implements CacheInterface
{
    /** @var string */
    protected $dir;

    /** @var string */
    protected $cacheFilename = '.env.cache.json';

    /** @var array */
    protected $cache = [];
    
    /**
     * @param string $dir
     * @throws InvalidCacheDirectoryException
     */
    public function __construct(string $dir) {
        if (!is_dir($dir)) {
            throw new InvalidCacheDirectoryException("Directory $dir was not found.");
        }
        $this->dir = rtrim($dir, ' \\/');
        $this->initializeCache();
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return isset($this->cache[$key]) && !$this->isCacheExpired($key);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->cache[$key]['content'];
        }
        return $default;
    }

    /**
     * @inheritDoc
     * @throws InvalidCacheItemException
     */
    public function set($key, $value, $ttl = null): bool
    {
        if ((!is_string($key) || $key === '') || !is_array($value)) {
            throw new InvalidCacheItemException("Cache item key must be a string and the value must be an array");
        }
        $mtime = strtotime(date('Y-m-d H:i:s'));
        $this->cache[$key] = [
            'mtime' => $mtime,
            'content' => $value
        ];
        $cache = json_encode($this->cache);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        $cacheFilepath = $this->dir . DIRECTORY_SEPARATOR . $this->cacheFilename;
        return file_put_contents($cacheFilepath, $cache);
    }
    
    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        if (!isset($this->cache[$key])) {
            return false;
        }
        unset($this->cache[$key]);
        $this->initializeCache();
        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->cache = [];
        $this->initializeCache();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys)) {
            return [];
        }
        $items = [];
        foreach ($keys as $key) {
            $this->items[$key] = $this->get($key);
        }
        return $items;
    }

    /**
     * @inheritDoc
     * @throws InvalidCacheItemException
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!is_array($values)) {
            return false;
        }

        foreach ($values as $value) {
            if (!is_array($value) || count($value) < 2) {
                throw new InvalidCacheItemException("Each cache item array must contain 2 values, the first as a key and the second as a value");
            }
            $this->set($value[0], $value[1]);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        if (!is_array($keys)) {
            return [];
        }
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }
    
    /**
     * creates the cache file if it does not exist and adds the current items to the file
     *
     * @return void
     */
    protected function initializeCache()
    {
        $cacheFilepath = $this->dir . DIRECTORY_SEPARATOR . $this->cacheFilename;
        if (!file_exists($cacheFilepath)) {
            file_put_contents($cacheFilepath, json_encode($this->cache));
            return;
        }
        $this->cache = json_decode(
            file_get_contents($cacheFilepath),
            true
        );
    }

    /**
     * checks if a cache item is expired 
     *
     * @param string $key
     * @return boolean
     */
    protected function isCacheExpired(string $key): bool
    {
        $fileInfo = new SplFileInfo($key);
        if ($fileInfo->getMTime() >= $this->cache[$key]['mtime']) {
            return true;
        }
        return false;
    }
}