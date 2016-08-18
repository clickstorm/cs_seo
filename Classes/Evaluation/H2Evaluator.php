<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class H2Evaluator extends AbstractEvaluator
{

	public function evaluate() {
		$state = self::STATE_RED;

		$count = $this->domDocument->getElementsByTagName('h2')->length;

		if($count > 0 && $count < 6) {
			$state =  self::STATE_GREEN;
		}

		return [
			'count' => $count,
			'state' => $state
		];
	}

}
