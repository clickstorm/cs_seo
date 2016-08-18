<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
abstract class AbstractLengthEvaluator extends AbstractEvaluator
{
	const MIN = 0;
	const MAX = 100;

	protected function evaluateLength($content) {
		$state = self::STATE_RED;

		$count = strlen($content);

		if($count > self::MIN && $count < self::MAX) {
			$state =  self::STATE_GREEN;
		} else {
			if($count > 0) {
				$state =  self::STATE_YELLOW;
			}
		}

		return [
			'state' => $state,
			'count' => $count
		];
	}
}
