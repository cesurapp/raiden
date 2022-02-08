<?php

namespace Package\SwooleBundle\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swoole\Table;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

class SwooleCacheAdapter implements AdapterInterface, CacheInterface, LoggerAwareInterface, ResettableInterface
{
    use LoggerAwareTrait;
    private ?Table $table;
    private static \Closure $createCacheItem;

    public function __construct(RequestStack $requestStack, private int $defaultLifetime = 0)
    {

        self::$createCacheItem ?? self::$createCacheItem = \Closure::bind(
            static function ($key, $value, $isHit) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;

                return $item;
            },
            null,
            CacheItem::class
        );
    }

    public function getItem(mixed $key): CacheItem
    {
        $item = $this->table->get($key);
        $isHit = isset($item['expr']) && $item['expr'] > time();

        return (self::$createCacheItem)($key, isset($item['value']) ? unserialize($item['value']) : null, $isHit ?? false);
    }

    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->getItems($keys);
        }
    }

    public function clear(string $prefix = ''): bool
    {
        $now = microtime(true);

        if ($prefix !== '') {
            foreach ($this->table as $key => $value) {
                if ($value['expr'] < $now && str_starts_with($key, $prefix)) {
                    $this->deleteItem($key);
                }
            }
            return true;
        }

        foreach ($this->table as $key => $value) {
            if ($value['expr'] < $now) {
                $this->deleteItem($key);
            }
        }

        return true;
    }

    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null): mixed
    {
        $item = $this->getItem($key);

        if (\INF === $beta || !$item->isHit()) {
            $save = true;
            $this->save($item->set($callback($item, $save)));
        }

        return $item->get();
    }

    public function hasItem(string $key): bool
    {
        return $this->table->exist($key);
    }

    public function delete(string $key): bool
    {
        return $this->deleteItem($key);
    }

    public function deleteItem(string $key): bool
    {
        return $this->table->del($key);
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            return false;
        }

        $item = (array)$item;

        $this->table->set($item["\0*\0key"], [
            'value' => serialize($item["\0*\0value"]),
            'expr' => (int)($item["\0*\0expiry"] ?? $this->defaultLifetime ?? PHP_INT_MAX)
        ]);

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }

    public function commit(): bool
    {
        return true;
    }

    public function reset()
    {
        foreach ($this->table as $key => $value) {
            $this->table->del($key);
        }
    }
}