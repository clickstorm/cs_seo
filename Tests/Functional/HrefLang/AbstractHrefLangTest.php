<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\HrefLang;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTest;

abstract class AbstractHrefLangTest extends AbstractFrontendTest
{
    /**
     * @test
     * @dataProvider checkHrefLangOutputDataProvider
     */
    public function checkHrefLangOutput(string $url, array $expectedTags, array $notExpectedTags): void
    {
        /** @var \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalResponse $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getBody();

        foreach ($expectedTags as $expectedTag) {
            self::assertStringContainsString($expectedTag, $content);
        }

        foreach ($notExpectedTags as $notExpectedTag) {
            self::assertStringNotContainsString($notExpectedTag, $content);
        }
    }

    /**
     * @return array
     */
    public function checkHrefLangOutputDataProvider(): array
    {
        return [];
    }
}
