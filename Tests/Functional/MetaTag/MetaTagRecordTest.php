<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\MetaTag;

class MetaTagRecordTest extends AbstractMetaTagTestCase
{
    public static function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [
            'category 1: with title and description fallback' => [
                'http://localhost/page-no-follow/category/1',
                [
                    'title' => 'Title of category 1',
                    'description' => 'No metadata record',
                    'og:type' => 'website',
                    'twitter:card' => 'summary',
                    'robots' => '',
                ],
            ],
            'category 2: hidden metadata' => [
                'http://localhost/page-no-follow/category/2',
                [
                    'title' => 'Title of category 2',
                    'description' => 'Has a hidden metadata record',
                ],
            ],
            'category 3: full metadata' => [
                'http://localhost/page-no-follow/category/3',
                [
                    'title' => 'SEO title category 3',
                    'description' => 'Description category 3',
                    'og:title' => 'OG title category 3',
                    'og:description' => 'OG description category 3',
                    'og:image' => '1920\-1080',
                    'twitter:title' => 'TW title category 3',
                    'twitter:description' => 'TW description category 3',
                    'twitter:image' => '1080\-1080',
                    'twitter:creator' => '@TW creator category 3',
                    'twitter:site' => '@TW site category 3',
                ],
            ],
            'category 4: with no index' => [
                'http://localhost/page-no-follow/category/4',
                [
                    'title' => 'Title of category 4',
                    'robots' => 'noindex,follow',
                ],
            ],
            'category 5: with no follow' => [
                'http://localhost/page-no-follow/category/5',
                [
                    'title' => 'Title of category 5',
                    'robots' => 'index,nofollow',
                ],
            ],
            'category 6: with no index and no follow' => [
                'http://localhost/page-no-follow/category/6',
                [
                    'title' => 'Title of category 6',
                    'robots' => 'noindex,nofollow',
                ],
            ],
        ];
    }
}
