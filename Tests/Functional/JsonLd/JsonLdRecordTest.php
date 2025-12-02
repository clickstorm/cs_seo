<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\JsonLd;

class JsonLdRecordTest extends AbstractJsonLdTestCase
{
    public static function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [
            'category 1: with title and description fallback' => [
                'http://localhost/category/1',
                '',
            ],
            'category 2: hidden metadata' => [
                'http://localhost/category/2',
                '',
            ],
            'category 3: full metadata' => [
                'http://localhost/category/3',
                '{"@context":"https://schema.org","@type":"Organization","url":"https://www.json-ld-test.com"}',
            ],
        ];
    }
}
