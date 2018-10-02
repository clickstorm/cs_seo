<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\TitleEvaluator;
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

/**
 * @package cs_seo
 */
class TitleEvaluatorTest extends UnitTestCase
{

    /**
     * @var TitleEvaluator
     */
    protected $generalEvaluationMock;

    /**
     * @var int
     */
    protected $min = 40;

    /**
     * @var int
     */
    protected $max = 57;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(TitleEvaluator::class, ['dummy'], [new \DOMDocument()]);
        $extConf = [
            'minTitle' => $this->min,
            'maxTitle' => $this->max
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'] = $extConf;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] = '';
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->generalEvaluationMock);
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']);
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils']);
    }

    /**
     * htmlspecialcharsOnArray Test
     *
     * @param string $html
     * @param mixed $expectedResult
     *
     * @dataProvider evaluateTestDataProvider
     * @return void
     * @test
     */
    public function evaluateTest($html, $expectedResult)
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $this->generalEvaluationMock->setDomDocument($domDocument);
        $result = $this->generalEvaluationMock->evaluate();

        ksort($expectedResult);
        ksort($result);

        $this->assertEquals(json_encode($expectedResult), json_encode($result));
    }

    /**
     * Dataprovider evaluateTest()
     *
     * @return array
     */
    public function evaluateTestDataProvider()
    {
        return [
            'zero title' => [
                '',
                [
                    'count' => 0,
                    'state' => TitleEvaluator::STATE_RED
                ]
            ],
            'count special chars' => [
                '<title>ÄÖÜß</title>',
                [
                    'count' => 4,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ]
            ],
            'short title' => [
                '<title>' . str_repeat('.', $this->min - 1) . '</title>',
                [
                    'count' => $this->min - 1,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ]
            ],
            'min good title' => [
                '<title>' . str_repeat('.', $this->min) . '</title>',
                [
                    'count' => $this->min,
                    'state' => TitleEvaluator::STATE_GREEN,
                ]
            ],
            'max good title' => [
                '<title>' . str_repeat('.', $this->max) . '</title>',
                [
                    'count' => $this->max,
                    'state' => TitleEvaluator::STATE_GREEN,
                ]
            ],
            'long title' => [
                '<title>' . str_repeat('.', $this->max + 1) . '</title>',
                [
                    'count' => $this->max + 1,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ]
            ]
        ];
    }
}
