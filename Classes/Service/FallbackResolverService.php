<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FallbackResolverService
{
    /**
     * @param array<string, mixed> $metaData
     * @param array<string, string> $fallbacks         // seoField => fallbackSpec
     * @param array<string, mixed> $record
     * @param array<string, mixed> $tableSettings      // expects 'table' and 'uid'
     * @return array<string, mixed>
     */
    public function applyFallbacks(array $metaData, array $fallbacks, array $record, array $tableSettings): array
    {
        foreach ($fallbacks as $seoField => $fallbackSpec) {
            if (!empty($metaData[$seoField])) {
                continue;
            }

            // 1) Direct field fallback
            if (!str_contains($fallbackSpec, '//') && !preg_match('/{([^}]+)}/', $fallbackSpec)) {
                if (!empty($record[$fallbackSpec])) {
                    $metaData[$seoField] = $this->resolveValue($seoField, (string)$record[$fallbackSpec], $tableSettings, $fallbackSpec);
                }
                continue;
            }

            // 2) Multiple fallbacks separated by //
            if (str_contains($fallbackSpec, '//')) {
                foreach (GeneralUtility::trimExplode('//', $fallbackSpec, true) as $possibleField) {
                    if (!empty($record[$possibleField]) && empty($metaData[$seoField])) {
                        $metaData[$seoField] = $this->resolveValue($seoField, (string)$record[$possibleField], $tableSettings, $possibleField);
                        break;
                    }
                }
                if (!empty($metaData[$seoField])) {
                    continue;
                }
            }

            // 3) Template with {field} placeholders
            if (preg_match('/{([^}]+)}/', $fallbackSpec)) {
                $result = $fallbackSpec;
                $matches = [];
                preg_match_all('/{([^}]+)}/', $fallbackSpec, $matches);
                $withBrackets = $matches[0] ?? [];
                $withoutBrackets = $matches[1] ?? [];
                foreach ($withBrackets as $i => $placeholder) {
                    $field = $withoutBrackets[$i] ?? '';
                    $value = isset($record[$field]) ? (string)$record[$field] : '';
                    $result = str_replace($placeholder, $value, $result);
                }
                if ($result !== '') {
                    $metaData[$seoField] = $this->sanitizeString($result);
                }
            }
        }

        // Final sanitize for string fields
        foreach ($metaData as $key => $value) {
            if (is_string($value)) {
                $metaData[$key] = $this->sanitizeString($value);
            }
        }

        return $metaData;
    }

    private function resolveValue(string $seoField, string $value, array $tableSettings, string $fallbackField): mixed
    {
        if ($seoField === 'og_image' || $seoField === 'tw_image') {
            return [
                'field' => $fallbackField,
                'table' => $tableSettings['table'] ?? '',
                'uid_foreign' => $tableSettings['uid'] ?? 0,
            ];
        }
        return $this->sanitizeString($value);
    }

    private function sanitizeString(string $value): string
    {
        return trim(strip_tags($value));
    }
}
