<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class EvaluationController
 */
class EvaluationController extends ActionController
{
    /**
     * @var EvaluationRepository
     */
    protected ?EvaluationRepository $evaluationRepository = null;

    public function showAction($uidForeign, $tableName = 'pages'): ResponseInterface
    {
        $evaluation = $this->evaluationRepository->findByUidForeignAndTableName($uidForeign, $tableName);

        $this->view->assign('results', $evaluation);

        return $this->htmlResponse($this->view->render());
    }

    public function injectEvaluationRepository(EvaluationRepository $evaluationRepository): void
    {
        $this->evaluationRepository = $evaluationRepository;
    }
}
