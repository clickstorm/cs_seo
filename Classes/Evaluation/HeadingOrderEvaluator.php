<?php

namespace Clickstorm\CsSeo\Evaluation;

use DOMNode;

class HeadingOrderEvaluator extends AbstractEvaluator
{
    protected array $headings = [];
    protected int $currentHeadingLevel = 0;
    protected int $state = self::STATE_YELLOW;
    protected int $count = 0;

    public function evaluate(): array
    {
        $this->checkNode($this->domDocument);


        return [
            'count' => $this->count,
            'state' => $this->state,
            'headings' => $this->headings
        ];
    }

    protected function checkNode(DOMNode $node): void
    {
        // If the node is a heading, check its level
        if ($node->nodeName[0] === 'h' && strlen($node->nodeName) === 2 && is_numeric($node->nodeName[1])) {
            $level = (int)$node->nodeName[1];
            $correctOrder = true;

            // set the state to green if one heading was found
            if ($this->state !== self::STATE_RED) {
                $this->state = self::STATE_GREEN;
            }

            // check if the level is correct
            if ($level > ($this->currentHeadingLevel + 1)) {
                $this->count++;
                $this->state = self::STATE_RED;
                $correctOrder = false;
            }

            $this->currentHeadingLevel = $level;

            // Store the heading text and whether its order is correct
            $this->headings[] = [
                'text' => $node->textContent,
                'level' => $level,
                'correctOrder' => $correctOrder
            ];
        }

        // Recursively check all child nodes
        foreach ($node->childNodes as $childNode) {
            $this->checkNode($childNode);
        }
    }
}
