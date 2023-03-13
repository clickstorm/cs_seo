<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

/**
 * since v12 the core also adds only route enhancer parameters to canonical and href lang
 *
 * @see https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-98488-TypolinkOptionAddQueryStringOnlyIncludesResolvedQueryArguments.html
 */
class CanonicalInvalidParamIOnTest extends AbstractCanonicalTest
{
    protected array $configurationToUseInTestInstance = [
        'FE' => [
            'cacheHash' => [
                'enforceValidation' => false
            ]
        ],
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => false,
            ],
        ],
    ];

    public function generateDataProvider(): array
    {
        return [
            'keep invalid param' => [
                'http://localhost/dummy-1-2-5?foo=bar',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5',
            ],
        ];
    }
}
