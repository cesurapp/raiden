<?php

namespace Package\ApiBundle\Response\Traits;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Package\ApiBundle\Exporter\DoctrineORMQuerySourceIterator;
use Sonata\Exporter\Writer\CsvWriter;
use Sonata\Exporter\Writer\XlsWriter;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportTrait
{
    private function isExport(Request $request, array $resource): bool
    {
        return $request->get('export') && array_filter($resource, static fn ($v) => isset($v['table']));
    }

    /**
     * Export to XLS | Csv.
     */
    private function exportStream(QueryBuilder|Query $builder, Request $request, array $resource): StreamedResponse
    {
        $resource = array_filter($resource, static fn ($v) => isset($v['table']));
        $fields = array_intersect(array_map('strtolower', $request->get('export_field', [])), array_keys($resource)) ?: array_keys($resource);

        // Source
        $source = new DoctrineORMQuerySourceIterator(
            $builder->getQuery(),
            $fields,
            array_map(static fn ($v) => $v['table'] ?? [], $resource)
        );

        // Writer
        $writer = match ($request->get('export')) {
            'xls' => new XlsWriter('php://output'),
            default => new CsvWriter('php://output')
        };

        // Response
        return new StreamedResponse(static function () use ($source, $writer, $resource) {
            $writer->open();

            foreach ($source as $index => $data) {
                // Write Label
                if (0 === $index) {
                    $fd = [];
                    foreach ($data as $key => $value) {
                        $fd[$resource[$key]['table']['label'] ?? $key] = $value;
                    }
                    $writer->write($fd);
                    continue;
                }

                // Data
                $writer->write($data);
            }

            $writer->close();
        }, 200, [
            'Content-Type' => $writer->getDefaultMimeType(),
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                'export.'.$writer->getFormat()
            ),
        ]);
    }
}
