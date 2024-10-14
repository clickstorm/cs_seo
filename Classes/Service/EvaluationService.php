<?php

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\DescriptionEvaluator;
use Clickstorm\CsSeo\Evaluation\H1Evaluator;
use Clickstorm\CsSeo\Evaluation\H2Evaluator;
use Clickstorm\CsSeo\Evaluation\HeadingOrderEvaluator;
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
    protected array $evaluators = [];

    protected ?EvaluationRepository $evaluationRepository = null;

    public function injectEvaluationRepository(EvaluationRepository $evaluationRepository): void
    {
        $this->evaluationRepository = $evaluationRepository;
    }

    public function getEvaluators(): array
    {
        return $this->evaluators;
    }

    public function setEvaluators(array $evaluators): void
    {
        $this->evaluators = $evaluators;
    }

    public function evaluate(string $html, string $keyword): array
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
    public function initEvaluators(): void
    {
        $evaluators = [];
        $extConf = ConfigurationUtility::getEmConfiguration();

        // default
        $availableEvaluators = [
            'Description' => DescriptionEvaluator::class,
            'H1' => H1Evaluator::class,
            'H2' => H2Evaluator::class,
            'HeadingOrder' => HeadingOrderEvaluator::class,
            'Images' => ImagesEvaluator::class,
            'Keyword' => KeywordEvaluator::class,
            'Title' => TitleEvaluator::class,
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

    protected function getFinalPercentage(array $results): array
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
            'count' => $count,
        ];
    }

    /**
     * return evaluation results of a specific page
     */
    public function getResults(array $record, string $table = ''): array
    {
        $results = [];
        $evaluation = $this->getEvaluation($record, $table);
        if ($evaluation) {
            $results = $evaluation->getResultsAsArray();
        }

        return $results;
    }

    public function getEvaluation(array|int $record, string $table = ''): ?Evaluation
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
            $recordId = (int)($record['uid'] ?? 0);
            $evaluation = $this->evaluationRepository->findByUidForeignAndTableName($recordId, 'pages');
        }

        return $evaluation;
    }
}
