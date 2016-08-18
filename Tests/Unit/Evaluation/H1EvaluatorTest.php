<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\H1Evaluator;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Abstract validator
 */
class H1EvaluatorTest extends UnitTestCase
{

	/**
	 * @var H1Evaluator
	 */
	protected $generalEvaluationMock;

	/**
	 * @return void
	 */
	public function setUp()
	{
		$this->generalEvaluationMock = $this->getAccessibleMock(H1Evaluator::class, ['dummy'], [new \DOMDocument()]);
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
		$restult = $this->generalEvaluationMock->evaluate();

		sort($expectedResult);
		sort($restult);

		$this->assertEquals($expectedResult, $restult);
	}

	/**
	 * Dataprovider evaluateTest()
	 *
	 * @return array
	 */
	public function evaluateTestDataProvider()
	{
		return [
			'zero h1' => [
				'',
				[
					'count' => 0,
					'state' => H1Evaluator::STATE_RED
				]
			],
			'one h1' => [
				'<html><body><h1>Headline One</h1></body></html>',
				[
					'count' => 1,
					'state' => H1Evaluator::STATE_GREEN,
				]
			],
			'two h1' => [
				'<h1>Headline One</h1><h1>Headline Two</h1>',
				[
					'state' => H1Evaluator::STATE_RED,
					'count' => 2
				]
			],
		];
	}

}
