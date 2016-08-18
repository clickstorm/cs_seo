<?php
namespace Clickstorm\CsSeo\Evaluation;


/**
 * Abstract validator
 */
abstract class AbstractEvaluator implements EvaluationInterface
{

	const STATE_GREEN = 2;
	const STATE_YELLOW = 1;
	const STATE_RED = 0;

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
	protected $bodyContent = '';

	/**
	 * TSFEUtility constructor.
	 * @param \DOMDocument $domDocument
	 * @param string $keyword
	 */
    public function __construct($domDocument, $keyword = '')
    {
	    $this->domDocument = $domDocument;
	    $this->keyword = $keyword;
    }

	/**
	 * @return \DOMDocument
	 */
	public function getDomDocument() {
		return $this->domDocument;
	}

	/**
	 * @param \DOMDocument $domDocument
	 */
	public function setDomDocument($domDocument) {
		$this->domDocument = $domDocument;
	}

	/**
	 * @return string
	 */
	public function getKeyword() {
		return $this->keyword;
	}

	/**
	 * @param string $keyword
	 */
	public function setKeyword($keyword) {
		$this->keyword = $keyword;
	}

	/**
	 * @param string $tagName
	 * @return string
	 */
	protected function getSingleDomElementContentByTagName($tagName) {
		$elements = $this->domDocument->getElementsByTagName($tagName);
		if($elements->item(0)) {
			return $elements->item(0)->textContent;
		} else {
			return '';
		}
	}

	/**
	 * @param $metaName
	 * @return int
	 */
	protected function getNumberOfMetaTags($metaName) {
		$counter = 0;
		$metaTags = $this->domDocument->getElementsByTagName('meta');

		/** @var \DOMElement $metaTag */
		foreach ($metaTags as $metaTag) {
			if($metaTag->getAttribute('name') == $metaName) {
				$counter++;
			}
		}

		return $counter;
	}

	protected function getMetaTagContent($metaName) {
		$content = '';
		$metaTags = $this->domDocument->getElementsByTagName('meta');

		/** @var \DOMElement $metaTag */
		foreach ($metaTags as $metaTag) {
			if($metaTag->getAttribute('name') == $metaName) {
				$content = $metaTag->getAttribute('content');
				break;
			}
		}

		return $content;
	}

	public function validate() {
		return [];
	}
}
