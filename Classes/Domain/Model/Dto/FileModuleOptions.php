<?php

namespace Clickstorm\CsSeo\Domain\Model\Dto;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileModuleOptions
{
    protected int $storageUid = 0;

    protected string $identifier = '';

    protected bool $includeSubfolders = true;

    protected bool $onlyReferenced = true;
    protected array $excludedImageExtensions = [];

    public function __construct(int $storageUid, string $identifier, bool $includeSubfolders, bool $onlyReferenced)
    {
        $this->storageUid = $storageUid;
        $this->identifier = $identifier;
        $this->includeSubfolders = $includeSubfolders;
        $this->onlyReferenced = $onlyReferenced;
        $this->excludedImageExtensions =
            GeneralUtility::trimExplode(',', ConfigurationUtility::getEmConfiguration()['excludeFileExtensions'] ?? '');
    }

    public function getStorageUid(): int
    {
        return $this->storageUid;
    }

    public function setStorageUid(int $storageUid): void
    {
        $this->storageUid = $storageUid;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function isIncludeSubfolders(): bool
    {
        return $this->includeSubfolders;
    }

    public function setIncludeSubfolders(bool $includeSubfolders): void
    {
        $this->includeSubfolders = $includeSubfolders;
    }

    public function getExcludedImageExtensions(): array
    {
        return $this->excludedImageExtensions;
    }

    public function setExcludedImageExtensions(array $excludedImageExtensions): void
    {
        $this->excludedImageExtensions = $excludedImageExtensions;
    }

    public function isOnlyReferenced(): bool
    {
        return $this->onlyReferenced;
    }

    public function setOnlyReferenced(bool $onlyReferenced): void
    {
        $this->onlyReferenced = $onlyReferenced;
    }
}
