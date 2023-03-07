<?php

namespace Package\SwooleBundle\Adapter;

use OpenSwoole\Table;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\PruneableInterface;

class SwooleCacheAdapter extends AbstractAdapter implements PruneableInterface
{
    protected ?Table $table;

    public function __construct(string $namespace = '', int $defaultLifetime = 0)
    {
        $this->table = isset($GLOBALS['httpServer']) ? $GLOBALS['httpServer']->appCache : null;
        parent::__construct($namespace, $defaultLifetime);
    }

    protected function doFetch(array $ids): iterable
    {
        $values = [];
        $now = time();

        foreach ($ids as $id) {
            $item = $this->table->get($this->getKey($id));
            if (!$item) {
                continue;
            }

            if ($now >= $item['expr']) {
                $this->table->del($this->getKey($id));
            } else {
                $values[$id] = unserialize($item['value']);
            }
        }

        return $values;
    }

    protected function doHave(string $id): bool
    {
        return ($item = $this->table->get($this->getKey($id))) && $item['expr'] > time();
    }

    protected function doClear(string $namespace): bool
    {
        foreach ($this->table as $id => $item) {
            if (str_starts_with($item['key'], $namespace)) {
                $this->table->del($id);
            }
        }

        return true;
    }

    protected function doDelete(array $ids): bool
    {
        foreach ($ids as $id) {
            $this->table->del($this->getKey($id));
        }

        return true;
    }

    protected function doSave(array $values, int $lifetime): array|bool
    {
        $expiresAt = $lifetime ? (time() + $lifetime) : 0;
        foreach ($values as $id => $value) {
            $this->table->set($this->getKey($id), [
                'value' => serialize($value),
                'expr' => $expiresAt,
                'key' => $id,
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

    private function getKey(string $key): string
    {
        return hash('xxh3', $key);
    }
}
