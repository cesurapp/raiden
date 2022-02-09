<?php

namespace Package\SwooleBundle\Adapter;

use Swoole\Table;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class SwooleCacheAdapter2 extends AbstractAdapter implements PruneableInterface
{
    protected Table $table;

    public function __construct(string $namespace = '', int $defaultLifetime = 0)
    {
        /** @phpstan-ignore-next-line */
        //$this->table = $kernel->getServer()->table;

        parent::__construct($namespace, $defaultLifetime);
    }

    protected function doFetch(array $ids): iterable
    {
        // TODO: Implement doFetch() method.
    }

    protected function doHave(string $id): bool
    {
        return $this->table->exist($id);
    }

    protected function doClear(string $namespace): bool
    {
        // TODO: Implement doClear() method.
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
        // TODO: Implement doSave() method.
    }

    public function prune(): bool
    {
        foreach ($this->table as $key => $value) {
            $this->table->del($key);
        }

        return true;
    }
}