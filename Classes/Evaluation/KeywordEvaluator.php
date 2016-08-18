<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class KeywordEvaluator extends AbstractEvaluator
{

	public function evaluate() {
		$results = [];

		$state = self::STATE_RED;

		if(empty($this->keyword)) {
			$results['notSet'] = 1;
		} else {
			$results['titleContains'] = substr_count($this->getSingleDomElementContentByTagName('title'), $this->keyword);
			$results['descriptionContains'] = substr_count($this->getMetaTagContent('description'), $this->keyword);
			$results['bodyContains'] = substr_count($this->getSingleDomElementContentByTagName('body'), $this->keyword);

			if($results['titleContains'] == 1 && $results['descriptionContains'] == 1 && $results['bodyContains'] > 0) {
				$state = self::STATE_GREEN;
			} else {
				$state = self::STATE_YELLOW;
			}
		}

		$results['state'] = $state;

		return $results;
	}

}
