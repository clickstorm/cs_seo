<?php

namespace Clickstorm\CsSeo\Evaluation;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class KeywordEvaluator extends AbstractEvaluator
{
    public function evaluate(): array
    {
        $results = [];

        $state = self::STATE_RED;

        if ($this->keyword === '' || $this->keyword === '0') {
            $results['notSet'] = 1;
        } else {
            $contains = ['title' => 0, 'description' => 0, 'body' => 0];

            $keywords = GeneralUtility::trimExplode(',', $this->keyword);

            foreach ($keywords as $keyword) {
                if (empty($keyword)) {
                    continue;
                }

                $contains['title'] += substr_count(
                    strtolower($this->getSingleDomElementContentByTagName('title')),
                    $keyword
                );
                $contains['description'] += substr_count(
                    strtolower($this->getMetaTagContent('description')),
                    $keyword
                );
                $contains['body'] += substr_count(
                    strtolower($this->getSingleDomElementContentByTagName('body')),
                    $keyword
                );
            }

            if ($contains['title'] > 0 && $contains['description'] > 0 && $contains['body'] > 0) {
                $state = self::STATE_GREEN;
            } else {
                $state = self::STATE_YELLOW;
            }
            $results['contains'] = $contains;
        }

        $results['state'] = $state;

        return $results;
    }
}
