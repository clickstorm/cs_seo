<?php

namespace Clickstorm\CsSeo\Evaluation\TCA;

class JsonLdEvaluator extends AbstractEvaluator
{
    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $is_in The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not.
     * @return string Evaluated field value
     */
    public function evaluateFieldValue($value, $is_in, &$set)
    {
        if($value && json_decode($value, true) === null) {
            $this->addFlashMessage(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:evaluation.tca.json_ld.invalid_json'
            );
        }
        return $value;
    }
}
