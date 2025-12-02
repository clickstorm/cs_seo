<?php

namespace Clickstorm\CsSeo\Evaluation;

/**
 * Class AbstractLengthEvaluator
 */
abstract class AbstractLengthEvaluator extends AbstractEvaluator
{
    protected function evaluateLength(string $content, int $min, int $max): array
    {
        $state = self::STATE_RED;

        $count = mb_strlen($content, 'UTF-8');

        if ($count >= $min && $count <= $max) {
            $state = self::STATE_GREEN;
        } elseif ($count > 0) {
            $state = self::STATE_YELLOW;
        }

        return [
            'state' => $state,
            'count' => $count,
        ];
    }
}
