<?php

namespace Clickstorm\CsSeo\Command;

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use Clickstorm\CsSeo\Service\EvaluationService;
use Clickstorm\CsSeo\Service\FrontendPageService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class EvaluationCommand extends Command
{
    protected ?EvaluationRepository $evaluationRepository = null;

    protected ?FrontendPageService $frontendPageService = null;

    protected ?PersistenceManager $persistenceManager = null;

    protected string $tableName = 'pages';

    public function injectEvaluationRepository(EvaluationRepository $evaluationRepository)
    {
        $this->evaluationRepository = $evaluationRepository;
    }

    public function injectFrontendPageService(FrontendPageService $frontendPageService)
    {
        $this->frontendPageService = $frontendPageService;
    }

    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * make the ajax update
     *
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function ajaxUpdate(ServerRequestInterface $request): ResponseInterface
    {
        // get parameter
        $table = '';
        $params = $request->getParsedBody();
        if (empty($params)) {
            $uid = $GLOBALS['GLOBALS']['HTTP_POST_VARS']['uid'];
            $table = $GLOBALS['GLOBALS']['HTTP_POST_VARS']['table'];
        } else {
            $uid = $params['uid'];
            $table = $params['table'] ?? '';
        }
        if ($table !== '') {
            $this->tableName = $table;
        }
        $this->processResults($uid);

        /** @var FlashMessageService $flashMessageService  */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('tx_csseo');

        return new HtmlResponse($flashMessageQueue->renderFlashMessages());
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function processResults(int $uid = 0, bool $localized = false): void
    {
        $items = $this->getAllItems($uid, $localized);
        $this->updateResults($items);

        if (!$localized) {
            $this->processResults($uid, true);
        }
    }

    protected function getAllItems(int $uid, bool $localized = false): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);

        $tcaCtrl = $GLOBALS['TCA'][$this->tableName]['ctrl'];
        $allowedDoktypes = ConfigurationUtility::getEvaluationDoktypes();

        // only with doktype page
        if ($this->tableName === 'pages') {
            $queryBuilder->andWhere($queryBuilder->expr()->in('doktype', $allowedDoktypes));
        }

        // check localization
        if ($localized) {
            if (isset($tcaCtrl['transForeignTable']) && !empty($tcaCtrl['transForeignTable'])) {
                $this->tableName = $tcaCtrl['transForeignTable'];
                $tcaCtrl = $GLOBALS['TCA'][$this->tableName]['ctrl'];
            } elseif (isset($tcaCtrl['languageField']) && !empty($tcaCtrl['languageField'])) {
                $queryBuilder->andWhere($queryBuilder->expr()->gt($tcaCtrl['languageField'], 0));
            }
        }

        // if single uid
        if ($uid > 0) {
            if ($localized && $tcaCtrl['transOrigPointerField']) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq(
                    $tcaCtrl['transOrigPointerField'],
                    $queryBuilder->createNamedParameter($uid, PDO::PARAM_INT)
                ));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, PDO::PARAM_INT)
                ));
            }
        }

        return $queryBuilder->select('*')->from($this->tableName)->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
     */
    protected function updateResults(array $items): void
    {
        foreach ($items as $item) {
            $frontendPage = $this->frontendPageService->getFrontendPage($item, $this->tableName);

            if (isset($frontendPage['content'])) {
                /** @var EvaluationService $evaluationUtility */
                $evaluationUtility = GeneralUtility::makeInstance(EvaluationService::class);

                $results = $evaluationUtility->evaluate($frontendPage['content'], $this->getFocusKeyword($item));

                $this->saveChanges($results, $item['uid'], $frontendPage['url']);
            }
        }
    }

    protected function getFocusKeyword(array $record): string
    {
        $keyword = '';
        if (isset($record['tx_csseo'])) {
            $metaTableName = 'tx_csseo_domain_model_meta';

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($metaTableName);

            $res = $queryBuilder->select('keyword')
                ->from($metaTableName)->where($queryBuilder->expr()->eq(
                'uid_foreign',
                $queryBuilder->createNamedParameter($record['uid'], PDO::PARAM_INT)
            ), $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($this->tableName)))->executeQuery();

            while ($row = $res->fetch()) {
                $keyword = $row['keyword'];
            }
        } else {
            $keyword = $record['tx_csseo_keyword'] ?? '';
        }

        return $keyword;
    }

     /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function saveChanges(array $results, int $uidForeign, string $url): void
    {
        /**
         * @var Evaluation|null $evaluation
         */
        $evaluation = $this->evaluationRepository->findByUidForeignAndTableName($uidForeign, $this->tableName);

        if (is_null($evaluation)) {
            $evaluation = GeneralUtility::makeInstance(Evaluation::class);
            $evaluation->setUidForeign($uidForeign);
            $evaluation->setTablenames($this->tableName);
        }

        $evaluation->setUrl($url);
        $evaluation->setResultsFromArray($results);

        if ($evaluation->_isNew()) {
            $this->evaluationRepository->add($evaluation);
        } else {
            $this->evaluationRepository->update($evaluation);
        }
        $this->persistenceManager->persistAll();
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('SEO evaluation of a single entry or the whole site')
            ->addArgument('tableName', InputArgument::OPTIONAL)
            ->addArgument('uid', InputArgument::OPTIONAL);
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->hasArgument('tableName') && !empty($input->getArgument('tableName'))) {
            $this->tableName = $input->getArgument('tableName');
        }
        $uid = $input->hasArgument('uid') ? (int)$input->getArgument('uid') : 0;
        $this->processResults($uid);

        return 0;
    }
}
