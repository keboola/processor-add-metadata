<?php

declare(strict_types=1);

namespace MyComponent;

use Keboola\Component\BaseComponent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
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
        $jsonDecode = new JsonDecode();
        $jsonEncode = new JsonEncode();
        /** @var Config $config */
        $config = $this->getConfig();
        $finder = new Finder();

        $finder->name('*.manifest')->in($inTablesFolder)->depth(0);
        foreach ($finder as $manifestFile) {
            $tableName = str_replace('.manifest', '', $manifestFile->getBasename());
            $this->getLogger()->debug(sprintf('Found manifest file: %s', $manifestFile->getBasename()));

            if ($config->hasTableMetadata($tableName)) {
                // read manifest
                try {
                    $manifest = $jsonDecode->decode(
                        (string) file_get_contents($manifestFile->getPathname()),
                        JsonEncoder::FORMAT,
                        [JsonDecode::ASSOCIATIVE => true]
                    );
                } catch (NotEncodableValueException $e) {
                    throw new \RuntimeException('Failed to read manifest: ' . $e->getMessage());
                }

                $metadataValue = $config->getMetadataValue($tableName);
                $metadataKey = $config->getMetadataKey();

                if (!array_key_exists('metadata', $manifest)) {
                    $manifest['metadata'] = [];
                }

                // add tag entry to metadata
                $manifest['metadata'][] = [
                    'key' => $metadataKey,
                    'value' => $metadataValue,
                ];

                $this->getLogger()->info(
                    sprintf(
                        'Adding metadata key: %s value: %s for table: %s',
                        $metadataKey,
                        $metadataValue,
                        $tableName
                    )
                );

                try {
                    file_put_contents(
                        $outTablesFolder . '/' . $manifestFile->getBasename(),
                        $jsonEncode->encode($manifest, JsonEncoder::FORMAT)
                    );
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
