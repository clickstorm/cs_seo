<?php
/**
 * Created by PhpStorm.
 * User: mhirdes
 * Date: 11.04.16
 * Time: 09:18
 */

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * own TSFE to render TSFE in the backend
 *
 * Class TSFEUtility
 * @package Clickstorm\CsSeo\Utility
 */
class EvaluationService {

    public function evaluate($html, $keyword) {
	    $results = [];
		$evaluators = ['H1', 'H2', 'Description', 'Title', 'Keyword', 'Images'];

	    $domDocument = new \DOMDocument;
	    @$domDocument->loadHTML($html);

	    foreach ($evaluators as $evaluator) {
	    	$class = 'Clickstorm\\CsSeo\\Evaluation\\' . $evaluator . 'Evaluator';
	    	$evaluatorInstance = GeneralUtility::makeInstance($class, $domDocument, $keyword);
			$results[$evaluator] = $evaluatorInstance->evaluate();
	    }

	    uasort($results, function($a, $b) {
		    return $a['state'] - $b['state'];
	    });

	    $results['Percentage'] = $this->getFinalPercentage($results);

	    return $results;
    }



	/**
	 * @param $results
	 * @return array
	 */
    protected function getFinalPercentage($results) {
    	$score = 0;
	    $state = AbstractEvaluator::STATE_RED;
		foreach ($results as $result) {
			$score += $result['state'];
		}

		$count = round($score / (count($results) * 2) * 100);

	    if($count == 100) {
	    	$state = AbstractEvaluator::STATE_GREEN;
	    } elseif($count > 40) {
		    $state = AbstractEvaluator::STATE_YELLOW;
	    }

	    return [
		    'state' => $state,
		    'count' => $count
	    ];
    }
}