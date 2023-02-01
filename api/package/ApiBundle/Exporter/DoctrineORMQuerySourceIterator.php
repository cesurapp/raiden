<?php

namespace Package\ApiBundle\Exporter;

use Doctrine\ORM\Query;
use Sonata\Exporter\Source\AbstractPropertySourceIterator;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyPath;

class DoctrineORMQuerySourceIterator extends AbstractPropertySourceIterator
{
    protected Query $query;

    /**
     * @param array<string> $fields Fields to export
     */
    public function __construct(
        Query $query,
        array $fields,
        private readonly array $fieldTemplate,
        string $dateTimeFormat = 'r',
        private readonly int $batchSize = 100
    ) {
        $this->query = clone $query;
        $this->query->setParameters($query->getParameters());
        foreach ($query->getHints() as $name => $value) {
            $this->query->setHint($name, $value);
        }

        parent::__construct($fields, $dateTimeFormat);
    }

    /**
     * @return array<string, mixed>
     */
    public function current(): array
    {
        $current = $this->getIterator()->current();

        $data = $this->getCurrentData($current);

        if (0 === ($this->getIterator()->key() % $this->batchSize)) {
            $this->query->getEntityManager()->clear();
        }

        return $data;
    }

    public function rewind(): void
    {
        $this->iterator = $this->iterableToIterator($this->query->toIterable());
        $this->iterator->rewind();
    }

    /**
     * @param array $iterable
     */
    private function iterableToIterator(iterable $iterable): \Iterator
    {
        if ($iterable instanceof \Iterator) {
            return $iterable;
        }
        if (\is_array($iterable)) {
            return new \ArrayIterator($iterable);
        }

        return new \ArrayIterator(iterator_to_array($iterable));
    }

    protected function getCurrentData(object|array $current): array
    {
        $data = [];
        foreach ($this->fields as $key => $field) {
            $name = \is_string($key) ? $key : $field;
            $propertyPath = $field;

            try {
                $val = $this->propertyAccessor->getValue($current, new PropertyPath($propertyPath));

                $data[$name] = isset($this->fieldTemplate[$name]['exporter']) ? $this->fieldTemplate[$name]['exporter']($val) : $this->getValue($val);
            } catch (UnexpectedTypeException) {
                // Non existent object in path will be ignored but a wrong path will still throw exceptions
                $data[$name] = null;
            }
        }

        return $data;
    }
}
