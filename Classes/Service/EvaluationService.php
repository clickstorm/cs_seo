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

	/**
	 * @var array
	 */
	protected $evaluators;

	/**
	 * @return array
	 */
	public function getEvaluators() {
		return $this->evaluators;
	}

	/**
	 * @param array $evaluators
	 */
	public function setEvaluators($evaluators) {
		$this->evaluators = $evaluators;
	}

	/**
	 * @TODO find a better solution for defaults
	 */
	public function initEvaluators() {
		$defaultEvaluators = [
			'H1' => \Clickstorm\CsSeo\Evaluation\H1Evaluator::class,
			'H2' => \Clickstorm\CsSeo\Evaluation\H2Evaluator::class,
			'Title' => \Clickstorm\CsSeo\Evaluation\TitleEvaluator::class,
			'Description' => \Clickstorm\CsSeo\Evaluation\DescriptionEvaluator::class,
			'Keyowrd' => \Clickstorm\CsSeo\Evaluation\KeyowrdEvaluator::class,
			'Images' => \Clickstorm\CsSeo\Evaluation\ImagesEvaluator::class
		];
		$this->evaluators = $defaultEvaluators;
	}

    public function evaluate($html, $keyword) {
	    $results = [];

	    $this->initEvaluators();

	    $domDocument = new \DOMDocument;
	    @$domDocument->loadHTML($html);

	    foreach ($this->evaluators as $evaluatorName => $evaluatorClass) {
	    	$evaluatorInstance = GeneralUtility::makeInstance($evaluatorClass, $domDocument, $keyword);
			$results[$evaluatorName] = $evaluatorInstance->evaluate();
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