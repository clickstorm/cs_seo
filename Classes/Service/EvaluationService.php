<?php

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Evaluation\H1Evaluator;
use Clickstorm\CsSeo\Evaluation\H2Evaluator;
use Clickstorm\CsSeo\Evaluation\TitleEvaluator;
use Clickstorm\CsSeo\Evaluation\DescriptionEvaluator;
use Clickstorm\CsSeo\Evaluation\KeywordEvaluator;
use Clickstorm\CsSeo\Evaluation\ImagesEvaluator;
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * service to evaluate a html page
 *
 * Class EvaluationService
 *
 */
class EvaluationService
{

    /**
     * @var array
     */
    protected $evaluators;

    /**
     * @return array
     */
    public function getEvaluators()
    {
        return $this->evaluators;
    }

    /**
     * @param array $evaluators
     */
    public function setEvaluators($evaluators)
    {
        $this->evaluators = $evaluators;
    }

    /**
     * @param string $html
     * @param string $keyword
     * @return array
     */
    public function evaluate($html, $keyword)
    {
        $results = [];

        $this->initEvaluators();

        $domDocument = new \DOMDocument;
        @$domDocument->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        foreach ($this->evaluators as $evaluatorName => $evaluatorClass) {
            $evaluatorInstance = GeneralUtility::makeInstance($evaluatorClass, $domDocument, $keyword);
            $results[$evaluatorName] = $evaluatorInstance->evaluate();
        }

        uasort(
            $results,
            function ($a, $b) {
                return $a['state'] - $b['state'];
            }
        );

        $results['Percentage'] = $this->getFinalPercentage($results);

        return $results;
    }

    /**
     * @TODO find a better solution for defaults
     */
    public function initEvaluators()
    {
        $evaluators = [];
        $extConf = ConfigurationUtility::getEmConfiguration();

        // default
        $availableEvaluators = [
            'H1' => H1Evaluator::class,
            'H2' => H2Evaluator::class,
            'Title' => TitleEvaluator::class,
            'Description' => DescriptionEvaluator::class,
            'Keyword' => KeywordEvaluator::class,
            'Images' => ImagesEvaluator::class
        ];

        // additional evaluators
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['evaluators'])) {
            $availableEvaluators =
                array_merge($availableEvaluators, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['evaluators']);
        }

        // select the final evaluators
        if (empty($extConf['evaluators'])) {
            $evaluators = $availableEvaluators;
        } else {
            foreach (GeneralUtility::trimExplode(',', $extConf['evaluators']) as $evaluator) {
                if (isset($availableEvaluators[$evaluator])) {
                    $evaluators[$evaluator] = $availableEvaluators[$evaluator];
                }
            }
        }

        $this->evaluators = $evaluators;
    }

    /**
     * @param $results
     *
     * @return array
     */
    protected function getFinalPercentage($results)
    {
        $score = 0;
        $count = 0;

        $state = AbstractEvaluator::STATE_RED;
        foreach ($results as $result) {
            $score += $result['state'];
        }

        $total = (count($results) * 2);

        if ($total > 0) {
            $count = round($score / $total * 100);
        }

        if ($count == 100) {
            $state = AbstractEvaluator::STATE_GREEN;
        } elseif ($count > 40) {
            $state = AbstractEvaluator::STATE_YELLOW;
        }

        return [
            'state' => $state,
            'count' => $count
        ];
    }
}
