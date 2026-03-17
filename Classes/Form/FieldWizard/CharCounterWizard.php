<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Form\FieldWizard;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

final class CharCounterWizard extends AbstractNode
{
    protected const L10N_LABELS = [
        'statusOk',
        'charsMissing',
        'charsOver',
        'chars',
        'recommendation.range'
    ];

    public function render(): array
    {
        $result = $this->initializeResultArray();

        $parameterArray = $this->data['parameterArray'] ?? [];
        $fieldWizardOptions = $this->data['renderData']['fieldWizardOptions'] ?? [];
        $fieldName = (string)($parameterArray['itemFormElName'] ?? '');
        $value = (string)($parameterArray['itemFormElValue'] ?? '');
        $minChars = (int)($fieldWizardOptions['minChars'] ?? 0);
        $maxChars = (int)($fieldWizardOptions['maxChars'] ?? 0);
        $labels = $this->getTranslatedLabels();
        $hint = '';

        if ($minChars > 0 || $maxChars > 0) {
            $hint = sprintf(
                '<div class="csseo-char-counter__hint">%s</div>',
                htmlspecialchars(sprintf($labels['recommendation.range'], $minChars, $maxChars), ENT_QUOTES)
            );
        }

        $result['html'] = sprintf(
            '<div class="csseo-char-counter__wrapper">
                <div class="csseo-char-counter badge badge-info | js-csseo-char-counter" data-field-name="%s" data-min-chars="%d" data-max-chars="%d" data-label-status-ok="%s" data-label-chars-missing="%s" data-label-chars-over="%s" data-label-chars="%s" aria-live="polite" role="status">
                    <span class="csseo-char-counter__value | js-csseo-char-counter-value">%d</span>
                    <span class="csseo-char-counter__message | js-csseo-char-counter-message">&nbsp;%s</span>
                </div>
                %s
            </div>',
            htmlspecialchars($fieldName, ENT_QUOTES),
            $minChars,
            $maxChars,
            htmlspecialchars($labels['statusOk'], ENT_QUOTES),
            htmlspecialchars($labels['charsMissing'], ENT_QUOTES),
            htmlspecialchars($labels['charsOver'], ENT_QUOTES),
            htmlspecialchars($labels['chars'], ENT_QUOTES),
            mb_strlen($value),
            $labels['chars'],
            $hint
        );

        // Include an ES module in the backend
        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create('@clickstorm/cs-seo/FormEngine/Wizard/CharCounter.js');

        return $result;
    }

    protected function getTranslatedLabels(): array
    {
        $labels = [];

        foreach (self::L10N_LABELS as $labelKey) {
            $labels[$labelKey] = GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:wizard.' . $labelKey
            );
        }

        return $labels;
    }
}
