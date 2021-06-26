<?php

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\Contracts\CacheManagerInterface;
use Abdeslam\DotEnv\Exceptions\InvalidCacheDirectoryException;
use SplFileInfo;

class CacheManager implements CacheManagerInterface
{
    /** @var string */
    protected $dir;

    protected $cacheFilename = '.env.cache.php';

    protected $cache = [];
    
    public function __construct(string $dir) {
        if (!is_dir($dir)) {
            throw new InvalidCacheDirectoryException("Directory $dir was not found.");
        }
        $this->dir = rtrim($dir, ' \\/');
        $this->initializeCache();
    }

    public function has(string $key)
    {
        return isset($this->cache[$key]) && !$this->isCacheExpired($key);
    }

    public function get(string $key)
    {
        if ($this->has($key)) {
            return $this->cache[$key]['content'];
        }
        return null;
    }

    public function set(string $key, array $value): bool
    {
        $mtime = strtotime(date('Y-m-d H:i:s'));
        $this->cache[$key] = [
            'mtime' => $mtime,
            'content' => $value
        ];
        $cache = json_encode($this->cache);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        return file_put_contents($this->cacheFilename, $cache);
    }

    protected function initializeCache()
    {
        $this->cacheFilename = $this->dir . DIRECTORY_SEPARATOR . '.env.cache.json';
        if (!file_exists($this->cacheFilename)) {
            file_put_contents($this->cacheFilename, json_encode($this->cache));
            return;
        }
        $this->cache = json_decode(
            file_get_contents($this->cacheFilename),
            true
        );
    }

    protected function isCacheExpired(string $key): bool
    {
        $fileInfo = new SplFileInfo($key);
        if ($fileInfo->getMTime() >= $this->cache[$key]['mtime']) {
            return true;
        }
        return false;
    }
}