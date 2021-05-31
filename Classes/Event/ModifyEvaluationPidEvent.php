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
}
