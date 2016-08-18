<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
class ImagesEvaluator extends AbstractEvaluator
{

	public function evaluate() {
		$state = self::STATE_RED;

		$images = $this->domDocument->getElementsByTagName('img');
		$count = $images->length;

		$altCount = 0;

		/** @var \DOMElement $element */
		foreach ($images as $element) {
			$alt = $element->getAttribute('alt');
			if(!empty($alt)) {
				$altCount++;
			}
		}

		if($count == $altCount) {
			$state = self::STATE_GREEN;
		} else {
			if($altCount > 0) {
				$state = self::STATE_YELLOW;
			}
		}

		return [
			'count' => $count,
			'altCount' => $altCount,
			'countWithoutAlt' => $count - $altCount,
			'state' => $state
		];
	}

}
