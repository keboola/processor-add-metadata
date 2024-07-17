<?php

declare(strict_types=1);

namespace Keboola\AddMetadataProcessor;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    /**
     * @return array<string>
     */
    public function getTablesNameList(): array
    {
        /** @var array<string, string[]> $tables */
        $tables = $this->getValue(['parameters', 'tables']);
        return array_map(function ($table) {
            return $table['table'];
        }, $tables);
    }

    /**
     * @return array{
     *      array{
     *          key: string,
     *          value: string
     *          }
     *     }|null[]
     */
    public function getMetadataForTable(string $tableName): array
    {
        /** @var array{
         *     array{
         *         table: string,
         *         metadata: array{
         *            array{
         *                key: string,
         *                value: string
         *            }
         *         }
         *     }
         * } $tables */
        $tables = $this->getValue(['parameters', 'tables']);

        foreach ($tables as $table) {
            if ($table['table'] === $tableName) {
                return $table['metadata'];
            }
        }

        return [];
    }
}
