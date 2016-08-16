<?php
/**
 * Created by PhpStorm.
 * User: mhirdes
 * Date: 11.04.16
 * Time: 09:18
 */

namespace Clickstorm\CsSeo\Utility;

use \Clickstorm\CsSeo\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * own TSFE to render TSFE in the backend
 *
 * Class TSFEUtility
 * @package Clickstorm\CsSeo\Utility
 */
class EvaluationUtility {

	const STATE_GREEN = 2;
	const STATE_YELLOW = 1;
	const STATE_RED = 0;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var \DOMDocument
     */
    protected $domDocument;

	/**
	 * @var string
	 */
	protected $keyword;

	/**
	 * TSFEUtility constructor.
	 * @param string $html
	 * @param string $keyword
	 */
    public function __construct($html, $keyword = '') {
        $this->html = $html;
	    $this->keyword = $keyword;

	    $this->domDocument = new \DOMDocument;
	    @$this->domDocument->loadHTML($html);
    }

    public function evaluate() {
	    $results = [];
		$results['h1'] = $this->evaluateH1();
		$results['h2'] = $this->evaluateH2();
	    $results['images'] = $this->evaluateImages();
	    if(!empty($this->keyword)) {
		    $results['keyword'] = $this->evaluateKeyword();
	    }

	    $results['percentage'] = $this->getFinalPercentage($results);

	    return $results;
    }

	/**
	 * @param $results
	 * @return float
	 */
    protected function getFinalPercentage($results) {
    	$score = 0;
		foreach ($results as $result) {
			$score += $result['state'];
		}

		return  round($score / (count($results) * 2) * 100, 1) ;

    }

    protected function evaluateH1() {
		$state = self::STATE_RED;

	    $count = $this->domDocument->getElementsByTagName('h1')->length;

	    if($count > 0 && $count < 2) {
		    $state =  self::STATE_GREEN;
	    }

	    return [
	    	'count' => $count,
		    'state' => $state
	    ];
	}

	protected function evaluateH2() {
		$state = self::STATE_RED;

		$count = $this->domDocument->getElementsByTagName('h2')->length;

		if($count > 0 && $count < 6) {
			$state =  self::STATE_GREEN;
		}

		return [
			'count' => $count,
			'state' => $state
		];
	}

	protected function evaluateImages() {
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

	protected function evaluateKeyword() {
		$results = [];

		$state = self::STATE_RED;

		$searchIn = ['title', 'description'];

		foreach ($searchIn as $tagName) {
			$results[$tagName . 'Contains'] = $this->checkIfNodeContains($tagName, $this->keyword);
		}

		$uniqueValues = array_unique($results);
		if(count($uniqueValues) == 1) {
			if($uniqueValues[0]) {
				$state = self::STATE_GREEN;
			}
		} else {
			$state = self::STATE_YELLOW;
		}

		$results['state'] = $state;

		return $results;
	}

	protected function checkIfNodeContains($tagName, $needle) {
		$tags = $this->domDocument->getElementsByTagName($tagName);

		/** @var \DOMElement $tag */
		foreach ($tags as $tag) {
			if(strpos($tag->nodeValue, $needle) !== false) {
				return true;
			}
		}
		return false;
	}
}