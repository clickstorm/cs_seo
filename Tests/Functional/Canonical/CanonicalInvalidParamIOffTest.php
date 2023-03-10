<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

class CanonicalInvalidParamIOffTest extends AbstractCanonicalTest
{
    protected bool $failOnFailure = true;

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => true,
            ],
        ],
    ];

    public function generateDataProvider(): array
    {
        return [
            // cs_seo tests
            'remove invalid param' => [
                'http://localhost/dummy-1-2-5?foo=bar',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5"/>' . chr(10),
            ],
            'keep valid speaking param' => [
                'http://localhost/dummy-1-2-5/category/1',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5/category/1"/>' . chr(10),
            ],
        ];
    }
}
