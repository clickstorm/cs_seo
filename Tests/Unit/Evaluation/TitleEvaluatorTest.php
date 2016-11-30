<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\TitleEvaluator;
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
class TitleEvaluatorTest extends UnitTestCase
{

	/**
	 * @var TitleEvaluator
	 */
	protected $generalEvaluationMock;

	/**
	 * @return void
	 */
	public function setUp()
	{
		$this->generalEvaluationMock = $this->getAccessibleMock(TitleEvaluator::class, ['dummy'], [new \DOMDocument()]);
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
			'zero title' => [
				'',
				[
					'count' => 0,
					'state' => TitleEvaluator::STATE_RED
				]
			],
			'short title' => [
				'<title>' . str_repeat('.', 39) . '</title>',
				[
					'count' => 39,
					'state' => TitleEvaluator::STATE_YELLOW,
				]
			],
			'min good title' => [
				'<title>' . str_repeat('.', 40) . '</title>',
				[
					'count' => 40,
					'state' => TitleEvaluator::STATE_GREEN,
				]
			],
			'max good title' => [
				'<title>' . str_repeat('.', 57) . '</title>',
				[
					'count' => 57,
					'state' => TitleEvaluator::STATE_GREEN,
				]
			],
			'long title' => [
				'<title>' . str_repeat('.', 58) . '</title>',
				[
					'count' => 58,
					'state' => TitleEvaluator::STATE_YELLOW,
				]
			]
		];
	}

}
