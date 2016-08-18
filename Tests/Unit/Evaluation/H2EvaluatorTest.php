<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\H2Evaluator;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Abstract validator
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
		$restult = $this->generalEvaluationMock->evaluate();

		sort($expectedResult);
		sort($restult);

		$this->assertEquals(json_encode($expectedResult), json_encode($restult));
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
