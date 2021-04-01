<?php

namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\DescriptionEvaluator;
use Nimut\TestingFramework\TestCase\UnitTestCase;

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

class DescriptionEvaluatorTest extends UnitTestCase
{

    /**
     * @var DescriptionEvaluator
     */
    protected $generalEvaluationMock;

    /**
     * @var int
     */
    protected $min = 140;

    /**
     * @var int
     */
    protected $max = 160;

    public function setUp()
    {
        $this->generalEvaluationMock =
            $this->getAccessibleMock(DescriptionEvaluator::class, ['dummy'], [new \DOMDocument()]);
        $extConf = [
            'minDescription' => $this->min,
            'maxDescription' => $this->max
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'] = $extConf;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] = '';
    }

    public function tearDown()
    {
        unset($this->generalEvaluationMock);
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']);
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils']);
    }

    /**
     * evaluateTest
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
            'zero description' => [
                '',
                [
                    'count' => 0,
                    'state' => DescriptionEvaluator::STATE_RED
                ]
            ],
            'short decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->min - 1) . '" />',
                [
                    'count' => $this->min - 1,
                    'state' => DescriptionEvaluator::STATE_YELLOW,
                ]
            ],
            'min good decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->min) . '" />',
                [
                    'count' => $this->min,
                    'state' => DescriptionEvaluator::STATE_GREEN,
                ]
            ],
            'max good decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->max) . '" />',
                [
                    'count' => $this->max,
                    'state' => DescriptionEvaluator::STATE_GREEN,
                ]
            ],
            'long decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->max + 1) . '" />',
                [
                    'count' => $this->max + 1,
                    'state' => DescriptionEvaluator::STATE_YELLOW,
                ]
            ]
        ];
    }
}
