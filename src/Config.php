<?php

declare(strict_types=1);

namespace Keboola\AddMetadataProcessor;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getMetadataForTable(string $tableName): array
    {
        $tables = $this->getValue(['parameters', 'tables']);

        foreach ($tables as $table) {
            if ($table['table'] === $tableName) {
                return $table['metadata'];
            }
        }

        return [];
    }
}
