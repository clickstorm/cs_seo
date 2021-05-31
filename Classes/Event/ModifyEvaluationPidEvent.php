<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Event;

/**
 * PSR-14 to change the pid of the evaluation
 */
final class ModifyEvaluationPidEvent
{
    /**
     * @var int
     */
    private $pid;

    protected $params;

    protected $tableName;

    protected $pageInfo;

    /**
     * ModifyEvaluationPidEvent constructor.
     * @param int $pid page id to call and modify
     * @param string $params additional params that are already defined
     * @param string $tableName the current table name to evaluate
     * @param array $pageInfo array with current page properties
     */
    public function __construct(int $pid, string $params, string $tableName, array $pageInfo)
    {
        $this->pid = $pid;
        $this->params = $params;
        $this->tableName = $tableName;
        $this->pageInfo = $pageInfo;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     */
    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return string
     */
    public function getParams(): string
    {
        return $this->params;
    }

    /**
     * @param string $params
     */
    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return array
     */
    public function getPageInfo(): array
    {
        return $this->pageInfo;
    }

    /**
     * @param array $pageInfo
     */
    public function setPageInfo(array $pageInfo): void
    {
        $this->pageInfo = $pageInfo;
    }
}
