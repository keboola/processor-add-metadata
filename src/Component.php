<?php

declare(strict_types=1);

namespace Keboola\AddMetadataProcessor;

use Keboola\Component\BaseComponent;
use Keboola\Component\JsonHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class Component extends BaseComponent
{
    protected function run(): void
    {
        $dataFolder = $this->getDataDir();
        $inTablesFolder = $dataFolder . '/in/tables';
        $outTablesFolder = $dataFolder . '/out/tables';
        $this->moveNotManifestFiles($inTablesFolder, $outTablesFolder);

        $fs = new Filesystem();
        $manifestManager = $this->getManifestManager();
        /** @var Config $config */
        $config = $this->getConfig();
        $finder = new Finder();

        $finder->name('*.manifest')->in($inTablesFolder)->depth(0);
        foreach ($finder as $manifestFile) {
            $tableName = str_replace('.manifest', '', $manifestFile->getBasename());
            $this->getLogger()->info(sprintf('Found manifest file: %s', $manifestFile->getBasename()));

            $metadataList = $config->getMetadataForTable($tableName);

            if (empty($metadataList)) {
                $this->getLogger()->notice(
                    sprintf('Move manifest file: %s without adding metadata', $manifestFile->getBasename())
                );
                $fs->rename($manifestFile->getPathname(), $outTablesFolder . '/' . $manifestFile->getBasename());
                continue;
            }

            // read manifest
            $manifest = $manifestManager->getTableManifest($tableName);

            if (empty($manifest['metadata'])) {
                $manifest['metadata'] = [];
            }

            foreach ($metadataList as $metadataPair) {
                // add entry to metadata
                $manifest['metadata'][] = $metadataPair;

                $this->getLogger()->info(
                    sprintf(
                        'Adding metadata key: %s value: %s for table: %s',
                        $metadataPair['key'],
                        $metadataPair['value'],
                        $tableName
                    )
                );
            }

            try {
                $this->getLogger()->notice(
                    sprintf('Saving updated manifest file: %s', $manifestFile->getBasename())
                );
                JsonHelper::writeFile($outTablesFolder . '/' . $manifestFile->getBasename(), $manifest);
            } catch (UnexpectedValueException $e) {
                throw new \RuntimeException(
                    sprintf('Failed to create manifest: %s', $e->getMessage())
                );
            }
        }
    }

    private function moveNotManifestFiles(string $sourcePath, string $outputPath): void
    {
        $fs = new Filesystem();

        // move folders
        $finder = new Finder();
        $finder->directories()->notName('*.manifest')->in($sourcePath)->depth(0);
        foreach ($finder as $sourceDirectory) {
            $fs->mkdir($outputPath);
            $fs->rename($sourceDirectory->getPathname(), $outputPath . '/' . $sourceDirectory->getBasename());
        }
        // move files
        $finder = new Finder();
        $finder->files()->notName('*.manifest')->in($sourcePath)->depth(0);
        foreach ($finder as $sourceFile) {
            $fs->mkdir($outputPath);
            $fs->rename($sourceFile->getPathname(), $outputPath . '/' . $sourceFile->getBasename());
        }
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }
}
