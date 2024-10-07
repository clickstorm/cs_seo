<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\MetaTag;

class MetaTagTest extends AbstractMetaTagTestCase
{
    public static function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [
            'page 1: with title and description' => [
                'http://localhost/',
                [
                    'title' => 'Title 1',
                    'description' => 'Description 1',
                    'og:type' => '{$plugin.tx_csseo.social.openGraph.type}',
                    'twitter:card' => 'summary',
                    'twitter:creator' => '@{$plugin.tx_csseo.social.twitter.creator}',
                    'twitter:site' => '@{$plugin.tx_csseo.social.twitter.site}',
                    'robots' => '',
                ],
            ],
            'page 2: with browser title and open graph' => [
                'http://localhost/page-with-og',
                [
                    'title' => 'Browser Title 2',
                    'description' => 'Description 2',
                    'og:title' => 'OG Title 2',
                    'og:description' => 'OG Description 2',
                    'og:image' => '1920\-1080',
                ],
            ],
            'page 3: with twitter cards' => [
                'http://localhost/page-with-twitter-cards',
                [
                    'title' => 'Title 3',
                    'description' => 'Description 3',
                    'twitter:title' => 'TW Title 3',
                    'twitter:description' => 'TW Description 3',
                    'twitter:image' => '1080\-1080',
                    'twitter:creator' => '@TW Creator 3',
                    'twitter:site' => '@TW Site 3',
                ],
            ],
            'page 4: with no index' => [
                'http://localhost/page-no-index',
                [
                    'title' => 'Title 4',
                    'robots' => 'noindex,follow',
                ],
            ],
            'page 5: with no follow' => [
                'http://localhost/page-no-follow',
                [
                    'title' => 'Title 5',
                    'robots' => 'index,nofollow',
                ],
            ],
            'page 6: with no index, no follow' => [
                'http://localhost/page-no-index-no-follow',
                [
                    'title' => 'Title 6',
                    'robots' => 'noindex,nofollow',
                ],
            ],
        ];
    }
}
