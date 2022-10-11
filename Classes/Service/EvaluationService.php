<?php

namespace Clickstorm\CsSeo\Service;

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

use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\DescriptionEvaluator;
use Clickstorm\CsSeo\Evaluation\H1Evaluator;
use Clickstorm\CsSeo\Evaluation\H2Evaluator;
use Clickstorm\CsSeo\Evaluation\ImagesEvaluator;
use Clickstorm\CsSeo\Evaluation\KeywordEvaluator;
use Clickstorm\CsSeo\Evaluation\TitleEvaluator;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * service to evaluate a html page
 *
 * Class EvaluationService
 */
class EvaluationService
{

    /**
     * @var array
     */
    protected $evaluators;

    /**
     * evaluationRepository
     *
     * @var EvaluationRepository
     */
    protected $evaluationRepository = null;

    /**
     * Inject a evaluationRepository
     *
     * @param EvaluationRepository $evaluationRepository
     */
    public function injectEvaluationRepository(EvaluationRepository $evaluationRepository)
    {
        $this->evaluationRepository = $evaluationRepository;
    }

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

        $domDocument = new \DOMDocument();
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
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['evaluators']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['evaluators'])) {
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
        $state = AbstractEvaluator::STATE_RED;
        foreach ($results as $result) {
            $score += $result['state'];
        }

        $total = (count($results) * 2);

        $count = $total > 0 ? (int)round($score / $total * 100) : 0;

        if ($count === 100) {
            $state = AbstractEvaluator::STATE_GREEN;
        } elseif ($count > 40) {
            $state = AbstractEvaluator::STATE_YELLOW;
        }

        return [
            'state' => $state,
            'count' => $count
        ];
    }

    /**
     * return evaluation results of a specific page
     *
     * @param $record
     * @param $table
     *
     * @return array
     */
    public function getResults($record, $table = '')
    {
        $results = [];
        $evaluation = $this->getEvaluation($record, $table);
        if ($evaluation) {
            $results = $evaluation->getResults();
        }

        return $results;
    }

    public function getEvaluation($record, $table = '')
    {
        if ($table) {
            $evaluation = $this->evaluationRepository->findByUidForeignAndTableName($record, $table);
        } elseif (isset($record['_PAGES_OVERLAY_LANGUAGE'])) {
            $evaluation =
                $this->evaluationRepository->findByUidForeignAndTableName(
                    $record['_PAGES_OVERLAY_UID'],
                    'pages'
                );
        } else {
            $evaluation = $this->evaluationRepository->findByUidForeignAndTableName((int)$record['uid'], 'pages');
        }

        return $evaluation;
    }
}
