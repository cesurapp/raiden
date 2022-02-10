<?php

namespace Package\SwooleBundle\Adapter;

use Swoole\Table;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\PruneableInterface;

/**
 * Swoole Table Symfony Cache Adapter.
 */
class SwooleCacheAdapter extends AbstractAdapter implements PruneableInterface
{
    protected ?Table $table;

    public function __construct(string $namespace = '', int $defaultLifetime = 0)
    {
        $this->table = isset($GLOBALS['http_server']) ? $GLOBALS['http_server']->table : null;
        parent::__construct($namespace, $defaultLifetime);
    }

    protected function doFetch(array $ids): iterable
    {
        $values = [];
        $now = time();

        foreach ($ids as $id) {
            $item = $this->table->get($id);
            if (!$item) {
                continue;
            }

            if ($now >= $item['expr']) {
                $this->table->del($id);
            } else {
                $values[$id] = unserialize($item['value']);
            }
        }

        return $values;
    }

    protected function doHave(string $id): bool
    {
        return ($item = $this->table->get($id)) && $item['expr'] > time();
    }

    protected function doClear(string $namespace): bool
    {
        foreach ($this->table as $key => $value) {
            if (str_starts_with($key, $namespace)) {
                $this->table->del($key);
            }
        }

        return true;
    }

    protected function doDelete(array $ids): bool
    {
        foreach ($ids as $id) {
            $this->table->del($id);
        }

        return true;
    }

    protected function doSave(array $values, int $lifetime): array|bool
    {
        $expiresAt = $lifetime ? (time() + $lifetime) : 0;
        foreach ($values as $id => $value) {
            $this->table->set($id, [
                'value' => serialize($value),
                'expr' => $expiresAt,
            ]);
        }

        return true;
    }

    public function prune(): bool
    {
        $time = time();
        $pruned = false;

        foreach ($this->table as $key => $item) {
            if ($time >= $item['expr']) {
                $this->table->del($key);
                $pruned = true;
            }
        }

        return $pruned;
    }
}
