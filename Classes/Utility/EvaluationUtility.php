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
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $bodyContent = '';

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
		$results['H1'] = $this->evaluateH1();
		$results['H2'] = $this->evaluateH2();
	    $results['Images'] = $this->evaluateImages();
	    $results['Title'] = $this->evaluateTitle();
	    $results['Description'] = $this->evaluateDescription();
	    $results['Keyword'] = $this->evaluateKeyword();

	    uasort($results, function($a, $b) {
		    return  $b['state'] - $a['state'];
	    });

	    $results['Percentage'] = $this->getFinalPercentage($results);

	    return $results;
    }

    protected function initBodyContent() {
		$body = $this->domDocument->getElementsByTagName('body');
	    $this->bodyContent = $body->item(0)->textContent;
    }

	/**
	 * @param $results
	 * @return float
	 */
    protected function getFinalPercentage($results) {
    	$score = 0;
	    $state = self::STATE_RED;
		foreach ($results as $result) {
			$score += $result['state'];
		}

		$count = round($score / (count($results) * 2) * 100);

	    if($count == 100) {
	    	$state = self::STATE_GREEN;
	    } elseif($count > 40) {
		    $state = self::STATE_YELLOW;
	    }

	    return [
		    'state' => $state,
		    'count' => $count
	    ];
    }

	protected function evaluateDescription() {
		$metaTags = $this->domDocument->getElementsByTagName('meta');

		/** @var \DOMElement $metaTag */
		foreach ($metaTags as $metaTag) {
			if($metaTag->getAttribute('name') == 'description') {
				$this->description = $metaTag->getAttribute('content');
				break;
			}
		}

		return $this->evaluateLength($this->description, 140, 160);
	}

    protected function evaluateTitle() {
	    $titleTag = $this->domDocument->getElementsByTagName('title');
	    $this->title = $titleTag->item(0)->nodeValue;
	    return $this->evaluateLength($this->title, 40, 57);
    }

    protected function evaluateLength($content, $min, $max) {
	    $state = self::STATE_RED;

	    $count = strlen($content);

	    if($count > $min && $count < $max) {
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

		if(empty($this->keyword)) {
			$results['notSet'] = 1;
		} else {
			$this->initBodyContent();

			$results['titleContains'] = substr_count($this->title, $this->keyword);
			$results['descriptionContains'] = substr_count($this->description, $this->keyword);
			$results['bodyContains'] = substr_count($this->bodyContent, $this->keyword);

			$uniqueValues = array_unique($results);
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