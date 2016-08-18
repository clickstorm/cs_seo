<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\DescriptionEvaluator;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Abstract validator
 */
class DescriptionEvaluatorTest extends UnitTestCase
{

	/**
	 * @var DescriptionEvaluator
	 */
	protected $generalEvaluationMock;

	/**
	 * @return void
	 */
	public function setUp()
	{
		$this->generalEvaluationMock = $this->getAccessibleMock(DescriptionEvaluator::class, ['dummy'], [new \DOMDocument()]);
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
			'zero description' => [
				'',
				[
					'count' => 0,
					'state' => DescriptionEvaluator::STATE_RED
				]
			],
			'short decription' => [
				'<meta name="description" content="' . str_repeat('.', 139) . '" />',
				[
					'count' => 139,
					'state' => DescriptionEvaluator::STATE_YELLOW,
				]
			],
			'min good decription' => [
				'<meta name="description" content="' . str_repeat('.', 140) . '" />',
				[
					'count' => 140,
					'state' => DescriptionEvaluator::STATE_GREEN,
				]
			],
			'max good decription' => [
				'<meta name="description" content="' . str_repeat('.', 160) . '" />',
				[
					'count' => 160,
					'state' => DescriptionEvaluator::STATE_GREEN,
				]
			],
			'long decription' => [
				'<meta name="description" content="' . str_repeat('.', 161) . '" />',
				[
					'count' => 161,
					'state' => DescriptionEvaluator::STATE_YELLOW,
				]
			]
		];
	}

}
