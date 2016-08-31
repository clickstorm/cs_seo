<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\KeywordEvaluator;
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
class KeywordEvaluatorTest extends UnitTestCase {

	/**
	 * @var KeywordEvaluator
	 */
	protected $generalEvaluationMock;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->generalEvaluationMock = $this->getAccessibleMock(
			KeywordEvaluator::class,
			['dummy'],
			[new \DOMDocument()]
		);
	}

	/**
	 * @return void
	 */
	public function tearDown() {
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
	public function evaluateTest($html, $keyword, $expectedResult) {
		$domDocument = new \DOMDocument();
		@$domDocument->loadHTML($html);
		$this->generalEvaluationMock->setDomDocument($domDocument);
		$this->generalEvaluationMock->setKeyword($keyword);
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
	public function evaluateTestDataProvider() {
		return [
			'no keyword' => [
				'',
				'',
				[
					'notSet' => 1,
					'state' => AbstractEvaluator::STATE_RED
				]
			],
			'keyword set, not found' => [
				'',
				'Test',
				[
					'contains' => [
						'title' => 0,
						'description' => 0,
						'body' => 0,
					],
					'state' => AbstractEvaluator::STATE_YELLOW
				]
			],
			'keyword different case, found in title' => [
				'<title>Test</title>',
				'test',
				[
					'contains' => [
						'title' => 1,
						'description' => 0,
						'body' => 0,
					],
					'state' => AbstractEvaluator::STATE_YELLOW
				]
			],
			'keyword set, found in title' => [
				'<title>Test</title>',
				'Test',
				[
					'contains' => [
						'title' => 1,
						'description' => 0,
						'body' => 0,
					],
					'state' => AbstractEvaluator::STATE_YELLOW
				]
			],
			'keyword set, found in description' => [
				'<meta name="description" content="Test">',
				'Test',
				[
					'contains' => [
						'title' => 0,
						'description' => 1,
						'body' => 0,
					],
					'state' => AbstractEvaluator::STATE_YELLOW
				]
			],
			'keyword set, found in body' => [
				'<body>Test</body>',
				'Test',
				[
					'contains' => [
						'title' => 0,
						'description' => 0,
						'body' => 1,
					],
					'state' => AbstractEvaluator::STATE_YELLOW
				]
			],
			'keyword set, found everywhere' => [
				'<head><title>Test</title><meta name="description" content="Test"></head><body>Test</body>',
				'Test',
				[
					'contains' => [
						'title' => 1,
						'description' => 1,
						'body' => 1,
					],
					'state' => AbstractEvaluator::STATE_GREEN
				]
			 ]
		];
	}
}
