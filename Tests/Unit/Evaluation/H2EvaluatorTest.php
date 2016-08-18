<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\H2Evaluator;
use TYPO3\CMS\Core\Tests\UnitTestCase;

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

class H2EvaluatorTest extends UnitTestCase
{

	/**
	 * @var H2Evaluator
	 */
	protected $generalEvaluationMock;

	/**
	 * @return void
	 */
	public function setUp()
	{
		$this->generalEvaluationMock = $this->getAccessibleMock(H2Evaluator::class, ['dummy'], [new \DOMDocument()]);
	}

	/**
	 * @return void
	 */
	public function tearDown()
	{
		unset($this->generalEvaluationMock);
	}

	/**
	 * htmlspecialcharsOnArray Test
	 *
	 * @param string $html
	 * @param mixed $expectedResult
	 * @dataProvider evaluateTestDataProvider
	 * @return void
	 * @test
	 */
	public function evaluateTest($html, $expectedResult) {
		$domDocument = new \DOMDocument();
		@$domDocument->loadHTML($html);
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
			'zero h2' => [
				'',
				[
					'count' => 0,
					'state' => H2Evaluator::STATE_RED
				]
			],
			'one h2' => [
				'<html><body><h2>Headline One</h2></body></html>',
				[
					'count' => 1,
					'state' => H2Evaluator::STATE_GREEN,
				]
			],
			'two h2' => [
				'<h2>Headline One</h2><h2>Headline Two</h2>',
				[
					'state' => H2Evaluator::STATE_GREEN,
					'count' => 2
				]
			],
			'six h2' => [
				str_repeat('<h2>Headline</h2>',6),
				[
					'state' => H2Evaluator::STATE_GREEN,
					'count' => 6
				]
			],
			'seven h2' => [
				str_repeat('<h2>Headline</h2>',7),
				[
					'state' => H2Evaluator::STATE_YELLOW,
					'count' => 7
				]
			],
		];
	}

}
