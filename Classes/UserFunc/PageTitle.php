<?php
/**
 * Created by PhpStorm.
 * User: mhirdes
 * Date: 11.04.16
 * Time: 11:56
 */

namespace Clickstorm\CsSeo\UserFunc;

use Clickstorm\CsSeo\Utility\TSFE;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageTitle {

    protected $TSFE;
    /**
     * @var array
     */
    protected $page;

    public function render($conf, $content) {

        $TSFEUtility = GeneralUtility::makeInstance(TSFE::class, $GLOBALS['TSFE']->id);
        $page = $TSFEUtility->getPage();
        $config = $TSFEUtility->getConfig();
        $separator =  $TSFEUtility->getPageTitleSeparator();
        $siteTitle =  $TSFEUtility->getSiteTitle();
        $title = empty($page['tx_csseo_title']) ? $page['title'] : $page['tx_csseo_title'];
        if(empty($page['tx_csseo_title_only'])) {
            if($config['pageTitleFirst']) {
                $title .= $separator . $siteTitle;
            } else {
                $title = $separator . $siteTitle . $title;
            }
        }

        return $title;
    }

    protected function getPage() {
        $this->page = $GLOBALS['TSFE']->page;
    }

} 