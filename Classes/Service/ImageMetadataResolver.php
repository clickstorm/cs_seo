<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Resolves a public image URL/path back to its sys_file record, including
 * processed-file lookups via sys_file_processedfile.original.
 *
 * Results are cached per request to avoid duplicate database queries when the
 * same URL is requested multiple times (e.g. by separate ViewHelpers in a
 * single template).
 */
class ImageMetadataResolver implements SingletonInterface
{
    /**
     * @var array<string, ?File>
     */
    private array $cache = [];

    public function resolve(string $url): ?File
    {
        if (isset($this->cache[$url]) || array_key_exists($url, $this->cache)) {
            return $this->cache[$url];
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return $this->cache[$url] = null;
        }

        $file = $this->resolveDirect($path) ?? $this->resolveFromProcessedFile($path);

        return $this->cache[$url] = $file;
    }

    private function resolveDirect(string $path): ?File
    {
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            $obj = $resourceFactory->retrieveFileOrFolderObject($path);
        } catch (\Throwable) {
            return null;
        }
        if ($obj instanceof ProcessedFile) {
            return $obj->getOriginalFile();
        }
        return $obj instanceof File ? $obj : null;
    }

    private function resolveFromProcessedFile(string $path): ?File
    {
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

        foreach ($storageRepository->findAll() as $storage) {
            $config = $storage->getConfiguration();
            $basePath = isset($config['basePath']) ? trim((string)$config['basePath'], '/') : '';
            if ($basePath === '') {
                continue;
            }

            $prefix = '/' . $basePath . '/';
            if (!str_starts_with($path, $prefix)) {
                continue;
            }

            $identifier = '/' . ltrim(substr($path, strlen($prefix)), '/');

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file_processedfile');
            $originalUid = $queryBuilder
                ->select('original')
                ->from('sys_file_processedfile')
                ->where(
                    $queryBuilder->expr()->eq(
                        'storage',
                        $queryBuilder->createNamedParameter((int)$storage->getUid(), Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'identifier',
                        $queryBuilder->createNamedParameter($identifier)
                    )
                )
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchOne();

            if (!$originalUid) {
                continue;
            }

            try {
                $file = $resourceFactory->getFileObject((int)$originalUid);
            } catch (\Throwable) {
                continue;
            }

            if ($file instanceof File) {
                return $file;
            }
        }

        return null;
    }
}
