<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class DescriptionEvaluator extends AbstractLengthEvaluator
{
	public function evaluate() {
		$this->getDomDocument()->getElementsByTagName('meta');

		$description = $this->getMetaTagContent('description');
		return $this->evaluateLength($description, 140, 160);
	}

}
