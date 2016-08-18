<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class TitleEvaluator extends AbstractLengthEvaluator
{
	public function evaluate() {
		$title = $this->getSingleDomElementContentByTagName('title');
		return $this->evaluateLength($title, 40, 57);
	}
}
