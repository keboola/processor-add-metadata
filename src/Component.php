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
            $this->getLogger()->debug(sprintf('Found manifest file: %s', $manifestFile->getBasename()));

            $metadata = $config->getMetadataForTable($tableName);

            if (null !== $metadata) {
                // read manifest
                $manifest = $manifestManager->getTableManifest($tableName);

                if (!array_key_exists('metadata', $manifest)) {
                    $manifest['metadata'] = [];
                }

                // add tag entry to metadata
                $manifest['metadata'][] = [
                    'key' => $metadata['key'],
                    'value' => $metadata['value'],
                ];

                $this->getLogger()->info(
                    sprintf(
                        'Adding metadata key: %s value: %s for table: %s',
                        $metadata['key'],
                        $metadata['value'],
                        $tableName
                    )
                );

                try {
                    JsonHelper::writeFile($outTablesFolder . '/' . $manifestFile->getBasename(), $manifest);
                } catch (UnexpectedValueException $e) {
                    throw new \RuntimeException(
                        sprintf('Failed to create manifest: %s', $e->getMessage())
                    );
                }
                $fs->remove($manifestFile->getPathname());
            } else {
                $this->getLogger()->info(
                    sprintf('Move manifest file: %s without tagging', $manifestFile->getBasename())
                );
                $fs->rename($manifestFile->getPathname(), $outTablesFolder . '/' . $manifestFile->getBasename());
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
