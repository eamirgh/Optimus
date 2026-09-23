<?php

namespace Eamirgh\Optimus\Security;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Closure;

class ConcurrencyLock
{
    public function __construct(
        protected CacheRepository $cache,
        protected int $defaultTimeout = 10
    ) {}

    /**
     * Execute a callback under an atomic lock to mitigate thundering herd.
     *
     * @template T
     * @param Closure(): T $callback
     * @return T
     */
    public function execute(string $key, Closure $callback, ?int $lockSeconds = null, int $waitSeconds = 10): mixed
    {
        $lockSeconds = $lockSeconds ?? $this->defaultTimeout;
        $lockKey = 'optimus:lock:' . md5($key);

        $store = $this->cache->getStore();

        // Check if lock method is supported on store
        if (method_exists($store, 'lock')) {
            $lock = $store->lock($lockKey, $lockSeconds);

            try {
                return $lock->block($waitSeconds, $callback);
            } catch (LockTimeoutException $e) {
                // Return result of callback as fallback if lock times out
                return $callback();
            }
        }

        // Fallback for stores without native lock implementation
        return $callback();
    }
}
