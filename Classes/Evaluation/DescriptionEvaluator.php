<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class DescriptionEvaluator extends AbstractLengthEvaluator
{
	const MIN = 140;
	const MAX = 160;

	public function evaluate() {
		$description = $this->getMetaTagContent('description');
		return $this->evaluateLength($description);
	}

}
