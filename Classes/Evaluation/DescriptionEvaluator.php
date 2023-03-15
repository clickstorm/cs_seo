<?php

namespace Clickstorm\CsSeo\Evaluation;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;

class DescriptionEvaluator extends AbstractLengthEvaluator
{
    public function evaluate(): array
    {
        $extConf = ConfigurationUtility::getEmConfiguration();

        $description = $this->getMetaTagContent('description');

        return $this->evaluateLength($description, $extConf['minDescription'], $extConf['maxDescription']);
    }
}
