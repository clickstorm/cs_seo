<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\JsonLd;

class JsonLdTest extends AbstractJsonLdTestCase
{
    public function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [
            'page 1: with JSON-LD' => [
                'http://localhost/',
                '{"@context": "https://schema.org","@type": "Organization","url": "https://www.json-ld-test.com"}',
            ],
            'page 2: without JSON-LD' => [
                'http://localhost/page-without-json-ld',
                '',
            ],
        ];
    }
}
