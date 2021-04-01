<?php

namespace Clickstorm\CsSeo\ViewHelpers;

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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetValueViewHelper
 */
class ImplodeViewHelper extends AbstractViewHelper
{

    /**
     * @param string $glue
     * @param array $pieces
     *
     * @return string
     */
    public function render()
    {
        $glue = $this->arguments['glue'];
        $pieces = $this->arguments['pieces'];
        return implode($glue, $pieces);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('glue', 'string', '', false, ',');
        $this->registerArgument('pieces', 'array', '', false);
    }
}
