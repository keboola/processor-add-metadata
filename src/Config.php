<?php

declare(strict_types=1);

namespace MyComponent;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getMetadataKey(): string
    {
        return $this->getValue(['parameters', 'metadata_key']);
    }

    public function hasTableMetadata(string $tableName): bool
    {
        return in_array($tableName, $this->getValue(['parameters', 'tables']));
    }

    public function getMetadataValue(string $tableName): string
    {
        $vendor = $this->getValue(['parameters', 'vendor']);
        $app = $this->getValue(['parameters', 'app']);
        return sprintf('bdm.%s.%s.%s', $vendor, $app, $tableName);
    }
}
