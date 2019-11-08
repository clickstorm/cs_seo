<?php

namespace Clickstorm\CsSeo\Evaluation\TCA;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 TYPO3 CodeFabrik <codeFabrik@techdivision.com>, TechDivision GmbH
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

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RobotsEvaluator
 *
 */
class RobotsDisallowAllEvaluator
{
    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $is_in The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not. Must be passed by reference
     *     and changed if needed.
     *
     * @return string Evaluated field value
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function evaluateFieldValue($value, $is_in, &$set)
    {
        if ($this->isRobotsDisallowed($value)) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $GLOBALS['LANG']->sL(
                    'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:evaluation.tca.robots_txt.robots_disallow_all'
                ),
                '',
                FlashMessage::WARNING
            );
            /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
        }

        return $value;
    }

    /**
     * Check if somebody made a disallow: / Statement
     *
     * @param string $value The field value to be evaluated
     *
     * @return bool
     */
    protected function isRobotsDisallowed($value)
    {
        $disallowed = false;
        // case-insensitive
        // disallow:[0-x Leerzeichen]/

        // preg:
        // "disallow:"
        // "\h*" horizontal whitespace (0 or more times)
        // "/"
        // "\h+" horizontal whitespace (0 or more times)
        // "\R" end of line
        // "/$" linebreak
        if (preg_match("~Disallow:\h*(/\h+|/\R|/$)~i", $value)) {
            $disallowed = true;
        }

        return $disallowed;
    }
}
