<?php

namespace Clickstorm\CsSeo\Evaluation\TCA;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class JsonLdEvaluator extends AbstractEvaluator
{
    private static bool $alreadyValidated = false;

    /**
     * Server-side validation/evaluation on saving the record
     *
     */
    public function evaluateFieldValue(string $value, string $is_in, bool &$set): string
    {
        if (self::$alreadyValidated || empty($value)) {
            return $value;
        }

        self::$alreadyValidated = true;

        // Remove surrounding <script> tag if present
        $value = $this->stripScriptWrapper($value);

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addFlashMessage(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:evaluation.tca.json_ld.invalid_json'
            );

            $invalidComment = LocalizationUtility::translate(
                'error.invalid_json_comment',
                'cs_seo'
            ) ?? '/* INVALID JSON */';

            return $invalidComment . "\n" . $value;
        }

        return $value;
    }

    /**
     * Remove <script type="application/ld+json"> wrapper from pasted code
     */
    protected function stripScriptWrapper(string $value): string
    {
        // Normalize whitespace and remove script wrapper
        $value = trim($value);

        // Match and extract the contents inside <script type="application/ld+json">...</script>
        if (preg_match('#<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $value, $matches)) {
            return trim($matches[1]);
        }

        return $value;
    }
}
