<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\ImagesEvaluator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class ImagesEvaluatorTest extends UnitTestCase
{
    /**
     * @var ImagesEvaluator
     */
    protected $generalEvaluationMock;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(
            ImagesEvaluator::class,
            ['dummy'],
            [new \DOMDocument()]
        );
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock);
    }

    /**
     * htmlspecialcharsOnArray Test
     *
     * @param string $html
     * @param mixed $expectedResult
     *
     * @dataProvider evaluateTestDataProvider
     * @test
     */
    public function evaluateTest($html, $expectedResult)
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);
        $this->generalEvaluationMock->setDomDocument($domDocument);
        $result = $this->generalEvaluationMock->evaluate();

        ksort($expectedResult);
        ksort($result);

        self::assertEquals(json_encode($expectedResult), json_encode($result));
    }

    /**
     * Dataprovider evaluateTest()
     *
     * @return array
     */
    public function evaluateTestDataProvider()
    {
        return [
            'zero images' => [
                '<html>',
                [
                    'count' => 0,
                    'altCount' => 0,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'one image no alt' => [
                '<img alt="" />',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => [''],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'one image with alt' => [
                '<img alt="Hallo" />',
                [
                    'count' => 1,
                    'altCount' => 1,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'one alt missing' => [
                '<img alt="" src="myImage.png" /><img alt="Test" />',
                [
                    'count' => 2,
                    'altCount' => 1,
                    'countWithoutAlt' => 1,
                    'images' => ['myImage.png'],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            '3 images with alt' => [
                str_repeat('<img alt="Test" />', 3),
                [
                    'count' => 3,
                    'altCount' => 3,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
        ];
    }
}
