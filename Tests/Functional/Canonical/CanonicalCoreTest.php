<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Moutnpoints point here to there original URL to avoid duplicated content
 */
class CanonicalCoreTest extends AbstractCanonicalTest
{
    public function generateDataProvider(): array
    {
        return [
            'uid: 1 with canonical_link' => [
                'http://localhost/',
                '<link rel="canonical" href="http://localhost/"/>' . chr(10),
            ],
            'uid: 2 with canonical_link' => [
                'http://localhost/dummy-1-2',
                '<link rel="canonical" href="http://localhost/dummy-1-2"/>' . chr(10),
            ],
            'uid: 3 with canonical_link AND content_from_pid = 2' => [
                'http://localhost/dummy-1-3',
                '<link rel="canonical" href="http://localhost/dummy-1-3"/>' . chr(10),
            ],
            'uid: 4 without canonical_link AND content_from_pid = 2' => [
                'http://localhost/dummy-1-4',
                '<link rel="canonical" href="http://localhost/dummy-1-2"/>' . chr(10),
            ],
            'uid: 5 without canonical_link AND without content_from_pid set' => [
                'http://localhost/dummy-1-2-5',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5"/>' . chr(10),
            ],
            'uid: 8 without canonical_link AND content_from_pid = 9 (but target page is hidden) results in no canonical' => [
                'http://localhost/dummy-1-2-8',
                '',
            ],
            'uid: 10 no index' => [
                'http://localhost/dummy-1-2-10',
                ''
            ],
            'uid: 11 with mount_pid_ol = 0' => [
                'http://localhost/dummy-1-2-11',
                '<link rel="canonical" href="http://localhost/dummy-1-2-11"/>' . chr(10),
            ],
            'uid: 12 with mount_pid_ol = 1' => [
                'http://localhost/dummy-1-2-12',
                '<link rel="canonical" href="http://localhost/dummy-1-2-12"/>' . chr(10),
            ],
            'subpage of page with mount_pid_ol = 0' => [
                'http://localhost/dummy-1-2-11/subpage-of-new-root',
                '<link rel="canonical" href="http://localhost/dummy-1-2-11/subpage-of-new-root"/>' . chr(10),
            ],
            'subpage of page with mount_pid_ol = 1' => [
                'http://localhost/dummy-1-2-12/subpage-of-new-root',
                '<link rel="canonical" href="http://localhost/dummy-1-2-12/subpage-of-new-root"/>' . chr(10),
            ],
            'uid: 14 typoscript setting config.disableCanonical' => [
                'http://localhost/no-canonical',
                ''
            ]
        ];
    }
}
