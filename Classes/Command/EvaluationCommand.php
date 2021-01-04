<?php

namespace Clickstorm\CsSeo\Command;

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

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use Clickstorm\CsSeo\Service\EvaluationService;
use Clickstorm\CsSeo\Service\FrontendPageService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class EvaluationCommand extends Command
{

    /**
     * evaluationRepository
     *
     * @var EvaluationRepository
     */
    protected $evaluationRepository = null;
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $persistenceManager;
    /**
     * @var string
     */
    protected $tableName = 'pages';

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
     * make the ajax update
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function ajaxUpdate(\Psr\Http\Message\ServerRequestInterface $request)
    {
        // @extensionScannerIgnoreLine
        $this->init();

        // get parameter
        $table = '';
        $params = $request->getParsedBody();
        if (empty($params)) {
            $uid = $GLOBALS['GLOBALS']['HTTP_POST_VARS']['uid'];
            $table = $GLOBALS['GLOBALS']['HTTP_POST_VARS']['table'];
        } else {
            $uid = $params['uid'];
            $table = $params['table'];
        }
        if ($table != '') {
            $this->tableName = $table;
        }
        $this->processResults($uid);

        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('tx_csseo');

        return new HtmlResponse($flashMessageQueue->renderFlashMessages());
    }

    protected function init()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->evaluationRepository = $this->objectManager->get(EvaluationRepository::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
    }

    /**
     * @param int $uid
     * @param bool $localized
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function processResults($uid = 0, $localized = false)
    {
        $items = $this->getAllItems($uid, $localized);
        $this->updateResults($items);

        if (!$localized) {
            $this->processResults($uid, true);
        }
    }

    /**
     * @param int $uid
     * @param bool $localized
     * @return array
     */
    protected function getAllItems($uid, $localized = false)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);

        $tcaCtrl = $GLOBALS['TCA'][$this->tableName]['ctrl'];
        $allowedDoktypes = ConfigurationUtility::getEvaluationDoktypes();

        // only with doktype page
        if ($this->tableName == 'pages') {
            $queryBuilder->andWhere($queryBuilder->expr()->in('doktype', $allowedDoktypes));
        }

        // check localization
        if ($localized) {
            if ($tcaCtrl['transForeignTable']) {
                $this->tableName = $tcaCtrl['transForeignTable'];
                $tcaCtrl = $GLOBALS['TCA'][$this->tableName]['ctrl'];
            } else {
                if ($tcaCtrl['languageField']) {
                    $queryBuilder->andWhere($queryBuilder->expr()->gt($tcaCtrl['languageField'], 0));
                }
            }
        }

        // if single uid
        if ($uid > 0) {
            if ($localized && $tcaCtrl['transOrigPointerField']) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq(
                    $tcaCtrl['transOrigPointerField'],
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ));
            }
        }

        $items = $queryBuilder->select('*')
            ->from($this->tableName)
            ->execute()
            ->fetchAll();

        return $items;
    }

    /**
     * @param $items
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function updateResults($items)
    {
        foreach ($items as $item) {
            /** @var FrontendPageService $frontendPageService */
            $frontendPageService = GeneralUtility::makeInstance(FrontendPageService::class, $item, $this->tableName);
            $frontendPage = $frontendPageService->getFrontendPage();

            if (isset($frontendPage['content'])) {
                /** @var EvaluationService $evaluationUtility */
                $evaluationUtility = GeneralUtility::makeInstance(EvaluationService::class);

                $results = $evaluationUtility->evaluate($frontendPage['content'], $this->getFocusKeyword($item));

                $this->saveChanges($results, $item['uid'], $frontendPage['url']);
            }
        }
    }

    /**
     * Get Keyword from record or page
     *
     * @param $record
     * @return string
     */
    protected function getFocusKeyword($record)
    {
        $keyword = '';
        if ($record['tx_csseo']) {
            $metaTableName = 'tx_csseo_domain_model_meta';

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($metaTableName);

            $res = $queryBuilder->select('keyword')
                ->from($metaTableName)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid_foreign',
                        $queryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($this->tableName))
                )
                ->execute();

            while ($row = $res->fetch()) {
                $keyword = $row['keyword'];
            }
        } else {
            $keyword = $record['tx_csseo_keyword'];
        }

        return $keyword;
    }

    /**
     * store the results in the db
     *
     * @param array $results
     * @param int $uidForeign
     * @param string $url
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function saveChanges($results, $uidForeign, $url)
    {
        /**
         * @var Evaluation $evaluation
         */
        $evaluation = $this->evaluationRepository->findByUidForeignAndTableName($uidForeign, $this->tableName);

        if (!$evaluation) {
            $evaluation = GeneralUtility::makeInstance(Evaluation::class);
            $evaluation->setUidForeign($uidForeign);
            $evaluation->setTablenames($this->tableName);
        }

        $evaluation->setUrl($url);
        $evaluation->setResults($results);

        if ($evaluation->_isNew()) {
            $this->evaluationRepository->add($evaluation);
        } else {
            $this->evaluationRepository->update($evaluation);
        }
        $this->persistenceManager->persistAll();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('SEO evaluation of a single entry or the whole site')
            ->addArgument('tableName', InputArgument::OPTIONAL)
            ->addArgument('uid', InputArgument::OPTIONAL);
    }

    /**
     * @param int $uid
     * @param string $tableName
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @extensionScannerIgnoreLine
        $this->init();

        if ($input->hasArgument('tableName') && !empty($input->getArgument('tableName'))) {
            $this->tableName = $input->getArgument('tableName');
        }
        $uid = $input->hasArgument('uid') ? (int)$input->getArgument('uid') : 0;
        $this->processResults($uid);

        return 0;
    }
}
