<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\ViewHelpers;

use Clickstorm\CsSeo\Service\ImageMetadataResolver;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Resolves an image URL to a backend edit URL for its sys_file_metadata record.
 *
 * Handles both regular sys_file paths and FAL processed file paths (e.g.
 * `/fileadmin/_processed_/...`) by looking up the original file via
 * `sys_file_processedfile.original`.
 *
 * Returns an empty string if the file cannot be resolved or the editor lacks
 * sufficient permissions; the calling template should fall back to the plain URL.
 */
class ImageEditUrlViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('url', 'string', 'Public URL or path of the image', true);
        $this->registerArgument('returnUrl', 'string', 'URL to return to after saving', false, '');
    }

    public function render(): string
    {
        $url = (string)$this->arguments['url'];
        $returnUrl = (string)$this->arguments['returnUrl'];

        $file = GeneralUtility::makeInstance(ImageMetadataResolver::class)->resolve($url);
        if ($file === null || !$file->checkActionPermission('editMeta')) {
            return '';
        }

        $metadataUid = (int)($file->getProperties()['metadata_uid'] ?? 0);
        if ($metadataUid === 0) {
            $file->getMetaData()->save();
            $metadataUid = (int)($file->getProperties()['metadata_uid'] ?? 0);
        }

        if ($metadataUid === 0) {
            return '';
        }

        $params = [
            'edit' => [
                'sys_file_metadata' => [
                    $metadataUid => 'edit',
                ],
            ],
        ];
        if ($returnUrl !== '') {
            $params['returnUrl'] = $returnUrl;
        }

        return (string)GeneralUtility::makeInstance(UriBuilder::class)
            ->buildUriFromRoute('record_edit', $params);
    }
}
