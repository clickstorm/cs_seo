<?php

namespace Clickstorm\CsSeo\Evaluation;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;

class H2Evaluator extends AbstractEvaluator
{
    public function evaluate(): array
    {
        $state = self::STATE_RED;
        $extConf = ConfigurationUtility::getEmConfiguration();
        $maxH2 = $extConf['maxH2'];

        $count = $this->domDocument->getElementsByTagName('h2')->length;

        if ($count > 0 && $count <= $maxH2) {
            $state = self::STATE_GREEN;
        } elseif ($count > $maxH2) {
            $state = self::STATE_YELLOW;
        }

        return [
            'count' => $count,
            'state' => $state,
        ];
    }
}
