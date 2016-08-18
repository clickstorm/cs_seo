<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class H1Evaluator extends AbstractEvaluator
{

	public function evaluate() {
		$state = self::STATE_RED;

		$count = $this->domDocument->getElementsByTagName('h1')->length;

		if($count > 0 && $count < 2) {
			$state =  self::STATE_GREEN;
		}

		return [
			'count' => $count,
			'state' => $state
		];
	}

}
