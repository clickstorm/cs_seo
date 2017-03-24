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
     * @var int
     */
    protected $uidForeign;

    /**
     * @var string
     */
    protected $tablenames;

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
}
