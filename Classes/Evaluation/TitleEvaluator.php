<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class TitleEvaluator extends AbstractLengthEvaluator
{
	const MIN = 40;
	const MAX = 57;

	public function evaluate() {
		$title = $this->getSingleDomElementContentByTagName('title');
		return $this->evaluateLength($title);
	}
}
