<?php
namespace Clickstorm\CsSeo\Tests\Utility;

use Clickstorm\CsSeo\Evaluation\TitleEvaluator;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Abstract validator
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
			'long decription' => [
				'<title>' . str_repeat('.', 58) . '</title>',
				[
					'count' => 58,
					'state' => TitleEvaluator::STATE_YELLOW,
				]
			]
		];
	}

}
