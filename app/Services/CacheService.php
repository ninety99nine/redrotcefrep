<?php

namespace App\Services;

use App\Enums\CacheName;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public string $key;

    public function __construct(CacheName $cacheName)
    {
        $this->key = $cacheName->value;
    }

    /**
     *  Make the cache name more unique by appending any string
     *
     *  @param string|null $string
     *  @return CacheService
     */
    public function append($string)
    {
        if(!empty($string)) {
            $this->key .= ':'.$string;
        }

        return $this;
    }

    /**
     *  @param \Closure|\DateTimeInterface|\DateInterval|int|null $ttl
     *  @param \Closure $callback
     *  @return mixed
     */
    public function remember($ttl, $callback)
    {
        return Cache::remember($this->key, $ttl, $callback);
    }

    /**
     *  @param \Closure $callback
     *  @return mixed
     */
    public function rememberForever($callback)
    {
        return Cache::rememberForever($this->key, $callback);
    }

    /**
     *  @param mixed $value
     *  @param \Closure|\DateTimeInterface|\DateInterval|int|null $ttl
     *  @return mixed
     */
    public function put($value, $ttl)
    {
        return Cache::put($this->key, $value, $ttl);
    }

    /**
     *  @return mixed
     */
    public function get()
    {
        return Cache::get($this->key);
    }

    /**
     *  @return bool
     */
    public function has()
    {
        return Cache::has($this->key);
    }

    /**
     *  @return bool
     */
    public function forget()
    {
        return Cache::forget($this->key);
    }

    /**
     *  @return bool
     */
    public function increment($value = 1)
    {
        return Cache::increment($this->key, $value);
    }

    /**
     *  @return int|bool
     */
    public function decrement($value = 1)
    {
        return Cache::decrement($this->key, $value);
    }
}
