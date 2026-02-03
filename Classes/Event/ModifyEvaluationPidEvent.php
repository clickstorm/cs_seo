<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Event;

/**
 * PSR-14 to change the pid of the evaluation
 */
final class ModifyEvaluationPidEvent
{
    /**
     * ModifyEvaluationPidEvent constructor.
     * @param int $pid page id to call and modify
     * @param string $params additional params that are already defined
     * @param string $tableName the current table name to evaluate
     * @param array $pageInfo array with current page properties
     */
    public function __construct(private int $pid, private readonly string $params, private readonly string $tableName, private readonly array $pageInfo)
    {
    }
    public function getPid(): int
    {
        return $this->pid;
    }
    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    public function getParams(): string
    {
        return $this->params;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getPageInfo(): array
    {
        return $this->pageInfo;
    }
}
