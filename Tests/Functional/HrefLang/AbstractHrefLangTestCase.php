<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\HrefLang;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

abstract class AbstractHrefLangTestCase extends AbstractFrontendTestCase
{
    #[Test]
    #[DataProvider('checkHrefLangOutputDataProvider')]
    public function checkHrefLangOutput(string $url, array $expectedTags, array $notExpectedTags): void
    {
        /** @var \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalResponse $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getBody();

        // check if page was found
        self::assertStringNotContainsString(self::$pageNotFoundString, $content);

        foreach ($expectedTags as $expectedTag) {
            self::assertStringContainsString($expectedTag, $content);
        }

        foreach ($notExpectedTags as $notExpectedTag) {
            self::assertStringNotContainsString($notExpectedTag, $content);
        }
    }

    public static function checkHrefLangOutputDataProvider(): array
    {
        return [];
    }
}
