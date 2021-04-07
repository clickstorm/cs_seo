<?php

namespace Clickstorm\CsSeo\Controller;

class ModuleFileController extends AbstractModuleController
{
    public static $session_prefix = 'tx_csseo_file_';
    public static $mod_name = 'file_CsSeoModFile';
    public static $uriPrefix = 'tx_csseo_file_csseomodfile';

    protected array $menuSetup = [
        'showEmptyImageAlt'
    ];

    public function showEmptyImageAltAction()
    {
        return $this->wrapModuleTemplate();
    }
}
