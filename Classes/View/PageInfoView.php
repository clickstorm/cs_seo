<?php
namespace Clickstorm\CsSeo\View;

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

use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Backend\View\PageLayoutView;

/**
 * Child class for the Web > Page module
 */
class PageInfoView extends \TYPO3\CMS\Backend\View\PageLayoutView {


    /**
     * @return string
     */
    public function getInfo()
    {
        $tbody = '';
        $thead = '';

        $delClause = BackendUtility::deleteClause('pages') . ' AND ' . $this->getBackendUser()->getPagePermsClause(1);
        $id = GeneralUtility::_GP('id');
        if (!$id) {
            // The root has a pseudo record in pageinfo...
            $row = $this->getPageLayoutController()->pageinfo;
        } else {
            $result = $this->getDatabase()->exec_SELECTquery('*', 'pages', 'uid=' . (int)$id . $delClause);
            $row = $this->getDatabase()->sql_fetch_assoc($result);
            BackendUtility::workspaceOL('pages', $row);
        }

        $this->setFieldArray();

//        $depth = (int)$this->getPageLayoutController()->MOD_SETTINGS['pages_levels'];
        $depth = 3;
        // Overriding a few things:
        $this->no_noWrap = 0;
        // Items
        $this->eCounter = $this->firstElementNumber;

        // Getting children:
        $theRows = array();
        $theRows = $this->pages_getTree($theRows, $row['uid'], $delClause . BackendUtility::versioningPlaceholderClause('pages'), '', $depth);
        if ($this->getBackendUser()->doesUserHaveAccess($row, 2)) {
            $editUids[] = $row['uid'];
        }
        $tbody .= $this->drawItem($row, $this->fieldArray);
        // Traverse all pages selected:
        foreach ($theRows as $sRow) {
            if ($this->getBackendUser()->doesUserHaveAccess($sRow, 2)) {
                $editUids[] = $sRow['uid'];
            }
            $tbody .= $this->drawItem($sRow, $this->fieldArray);
        }
        $this->eCounter++;

        // Traverse fields (as set above) in order to create header values:
        foreach ($this->fieldArray as $field) {
            $thead .= '<td>'
                    . $this->getLanguageService()->sL($GLOBALS['TCA']['pages']['columns'][$field]['label'], true)
                    . '</td>';
        }

        $out = '<div class="table-responsive"><table class="table table-striped">' .
                    '<thead><tr>' .
                       $thead .
                    '</tr></thead>' .
                    '<tbody>' .
                        $tbody .
                    '</tbody>' .
                '</table></div>';

        return $out;
    }

    /**
     * Adds a list item for the pages-rendering
     *
     * @param array $row Record array
     * @param array $fieldArr Field list
     * @return string HTML for the item
     */
    public function drawItem($row, $fieldArr)
    {
        // Initialization
        $theIcon = $this->getIcon('pages', $row);
        // Preparing and getting the data-array
        $theData = array();
        foreach ($fieldArr as $field) {
            switch ($field) {
                case 'title':
                    $pTitle = htmlspecialchars(BackendUtility::getProcessedValue('pages', $field, $row[$field], 20));
                    $theData[$field] = $row['treeIcons'] . $pTitle . '&nbsp;&nbsp;';
                    break;
                case 'uid':
                    if ($this->getBackendUser()->doesUserHaveAccess($row, 2)) {
                        $urlParameters = [
                            'edit' => [
                                'pages' => [
                                    $row['uid'] => 'edit'
                                ]
                            ],
                            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
                        ];
                        $url = BackendUtility::getModuleUrl('record_edit', $urlParameters);
                        $eI = '<a href="' . htmlspecialchars($url)
                            . '" title="' . $this->getLanguageService()->getLL('editThisPage', true) . '">'
                            . $this->iconFactory->getIcon('actions-document-open', Icon::SIZE_SMALL)->render() . '</a>';
                    } else {
                        $eI = '';
                    }
                    $theData[$field] = '<span align="right">' . $row['uid'] . $eI . '</span>';
                    break;
                default:
                    $theData[$field] = $this->getPagesTableFieldValue($field, $row);
            }
        }
        $this->addElement_tdParams['title'] = $row['_CSSCLASS'] ? ' class="' . $row['_CSSCLASS'] . '"' : '';
        return $this->addRow(1, $theData);
    }

    /**
     * Returns a table-row with the content from the fields in the input data array.
     * OBS: $this->fieldArray MUST be set! (represents the list of fields to display)
     *
     * @param int $h Is an integer >=0 and denotes how tall an element is. Set to '0' makes a halv line, -1 = full line, set to 1 makes a 'join' and above makes 'line'
     * @param array $data Is the dataarray, record with the fields. Notice: These fields are (currently) NOT htmlspecialchar'ed before being wrapped in <td>-tags
     * @return string HTML content for the table row
     */
    public function addRow($h, $data)
    {
        $colType = 'td';
        $noWrap = $this->no_noWrap ? '' : ' nowrap="nowrap"';
        // Start up:
        $parent = isset($data['parent']) ? (int)$data['parent'] : 0;
        $out = '
		<!-- Element, begin: -->
		<tr data-uid="' . (int)$data['uid'] . '" data-l10nparent="' . $parent . '">';
        // Init rendering.
        $colsp = '';
        $lastKey = '';
        $c = 0;
        $ccount = 0;
        // __label is used as the label key to circumvent problems with uid used as label (see #67756)
        // as it was introduced later on, check if it really exists before using it
        $fields = $this->fieldArray;
        if ($colType === 'td' && array_key_exists('__label', $data)) {
            $fields[0] = '__label';
        }
        // Traverse field array which contains the data to present:
        foreach ($fields as $vKey) {
            if (isset($data[$vKey])) {
                if ($lastKey) {
                    $cssClass = $this->addElement_tdCssClass[$lastKey];
                    if ($this->oddColumnsCssClass && $ccount % 2 == 0) {
                        $cssClass = implode(' ', array($this->addElement_tdCssClass[$lastKey], $this->oddColumnsCssClass));
                    }
                    $out .= '
						<' . $colType . $noWrap . ' class="' . $cssClass . '"' . $colsp . $this->addElement_tdParams[$lastKey] . '>' . $data[$lastKey] . '</' . $colType . '>';
                }
                $lastKey = $vKey;
                $c = 1;
                $ccount++;
            } else {
                if (!$lastKey) {
                    $lastKey = $vKey;
                }
                $c++;
            }
            if ($c > 1) {
                $colsp = ' colspan="' . $c . '"';
            } else {
                $colsp = '';
            }
        }
        if ($lastKey) {
            $cssClass = $this->addElement_tdCssClass[$lastKey];
            if ($this->oddColumnsCssClass) {
                $cssClass = implode(' ', array($this->addElement_tdCssClass[$lastKey], $this->oddColumnsCssClass));
            }
            $out .= '
				<' . $colType . $noWrap . ' class="' . $cssClass . '"' . $colsp . $this->addElement_tdParams[$lastKey] . '>' . $data[$lastKey] . '</' . $colType . '>';
        }
        // End row
        $out .= '
		</tr>';
        // Return row.
        return $out;
    }

    protected function setFieldArray() {
        $this->fieldArray = [
            'title',
            'description',
            'tx_csseo_title',
            'tx_csseo_title_only'];
    }


}
