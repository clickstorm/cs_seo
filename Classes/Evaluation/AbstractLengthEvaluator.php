<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
abstract class AbstractLengthEvaluator extends AbstractEvaluator
{
	protected function evaluateLength($content, $min, $max) {
		$state = self::STATE_RED;

		$count = strlen($content);

		if($count >= $min && $count <= $max) {
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
