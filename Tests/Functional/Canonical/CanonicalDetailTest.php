<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Moutnpoints point here to there original URL to avoid duplicated content
 */
class CanonicalDetailTest extends AbstractCanonicalTest
{
    protected $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => true,
            ],
        ],
        'FE' => [
            'additionalCanonicalizedUrlParameters' => 'category',
        ],
    ];

    public function generateDataProvider(): array
    {
        return [
            'sys_category: 1 without metadata' => [
                'http://localhost/dummy-1-2-5/category/1',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5/category/1"/>',
            ],
            'sys_category: 2 with hidden metadata' => [
                'http://localhost/dummy-1-2-5/category/2',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5/category/2',
            ],
            'sys_category: 3 with canonical from metadata' => [
                'http://localhost/dummy-1-2-5/category/3',
                '<link rel="canonical" href="http://canonical-of-record.org/3"/>',
            ],
            'sys_category: 4 no_index from metada' => [
                'http://localhost/dummy-1-2-5/category/4',
                '',
            ],
        ];
    }
}
