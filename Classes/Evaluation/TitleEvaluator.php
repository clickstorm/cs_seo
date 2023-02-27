<?php

namespace Clickstorm\CsSeo\Evaluation;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;

class TitleEvaluator extends AbstractLengthEvaluator
{
    public function evaluate(): array
    {
        $title = $this->getSingleDomElementContentByTagName('title');
        $extConf = ConfigurationUtility::getEmConfiguration();

        return $this->evaluateLength($title, $extConf['minTitle'], $extConf['maxTitle']);
    }
}
