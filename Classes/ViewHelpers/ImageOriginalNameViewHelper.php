<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\ViewHelpers;

use Clickstorm\CsSeo\Service\ImageMetadataResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the original filename for a given public image URL/path.
 * Falls back to the URL's basename if the file cannot be resolved.
 */
class ImageOriginalNameViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('url', 'string', 'Public URL or path of the image', true);
        $this->registerArgument('withPath', 'bool', 'Return identifier (path) instead of just name', false, false);
    }

    public function render(): string
    {
        $url = (string)$this->arguments['url'];
        $file = GeneralUtility::makeInstance(ImageMetadataResolver::class)->resolve($url);

        if ($file === null) {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            return basename($path);
        }

        if ($this->arguments['withPath']) {
            return $file->getIdentifier();
        }

        return $file->getName();
    }
}
