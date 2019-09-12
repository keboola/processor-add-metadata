<?php

declare(strict_types=1);

namespace Keboola\AddMetadataProcessor;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getMetadataForTable(string $tableName): ?array
    {
        $metadata = $this->getValue(['parameters', 'metadata']);

        foreach ($metadata as $meta) {
            if ($meta['table'] === $tableName) {
                return $meta;
            }
        }

        return null;
    }
}
