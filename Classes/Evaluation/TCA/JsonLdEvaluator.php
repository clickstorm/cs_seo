<?php

namespace Clickstorm\CsSeo\Evaluation\TCA;

class JsonLdEvaluator extends AbstractEvaluator
{
    /**
     * Server-side validation/evaluation on saving the record
     *
     */
    public function evaluateFieldValue(string $value, string $is_in, bool &$set): string
    {
        if ($value && !isset($_REQUEST['tx_csseo_json_ld_eval_done'])) {
            $value = trim(preg_replace('#<script(.*?)>|</script>#is', '', $value));
            if ($value && json_decode($value, true) === null) {
                $this->addFlashMessage(
                    'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:evaluation.tca.json_ld.invalid_json'
                );
            }
        }

        $_REQUEST['tx_csseo_json_ld_eval_done'] = true;

        return $value;
    }
}
