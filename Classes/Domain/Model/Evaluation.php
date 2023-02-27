<?php

namespace Clickstorm\CsSeo\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Evaluation
 */
class Evaluation extends AbstractEntity
{
    protected string $results = '';

    protected string $url = '';

    protected int $uidForeign = 0;

    protected string $tablenames = '';

    protected int $tstamp = 0;

    public function getResults(): string
    {
        return $this->results;
    }

    public function setResults(): void
    {
        $this->results = $results;
    }

    public function getResultsAsArray(): array
    {
        $results = unserialize($this->results);

        return is_array($results) ? $results : [];
    }

    public function setResultsFromArray(array $results): void
    {
        $this->results = serialize($results);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUidForeign(): int
    {
        return $this->uidForeign;
    }

    public function setUidForeign(int $uidForeign): void
    {
        $this->uidForeign = $uidForeign;
    }

    public function getTablenames(): string
    {
        return $this->tablenames;
    }

    public function setTablenames(string $tablenames): void
    {
        $this->tablenames = $tablenames;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }
}
