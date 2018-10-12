<?php

namespace Clickstorm\CsSeo\Domain\Model;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes
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

/**
 * Class Evaluation
 *
 * @package Clickstorm\CsSeo\Domain\Model
 */
class Evaluation extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $results;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $uidForeign;

    /**
     * @var string
     */
    protected $tablenames;

    /**
     * @var int
     */
    protected $tstamp;

    /**
     * @return array
     */
    public function getResults()
    {
        return unserialize($this->results);
    }

    /**
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results = serialize($results);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return int
     */
    public function getUidForeign()
    {
        return $this->uidForeign;
    }

    /**
     * @param int $uidForeign
     */
    public function setUidForeign($uidForeign)
    {
        $this->uidForeign = $uidForeign;
    }

    /**
     * @return string
     */
    public function getTablenames()
    {
        return $this->tablenames;
    }

    /**
     * @param string $tablenames
     */
    public function setTablenames($tablenames)
    {
        $this->tablenames = $tablenames;
    }

    /**
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * @param int $tstamp
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
    }
}
