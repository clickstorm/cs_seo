<?php
declare(strict_types=1);

namespace Clickstorm\CsSeo\Form\FieldWizard;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

final class CharCounterWizard extends AbstractNode
{
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $parameterArray = $this->data['parameterArray'] ?? [];
        $fieldName = (string)($parameterArray['itemFormElName'] ?? '');
        $value = (string)($parameterArray['itemFormElValue'] ?? '');

        $result['html'] = sprintf(
            '<div class="csseo-char-counter badge badge-info | js-csseo-char-counter" data-field-name="%s" aria-live="polite" role="status">
                <span class="csseo-char-counter__value | js-csseo-char-counter-value">%d</span>&nbsp;%s
            </div>',
            htmlspecialchars($fieldName, ENT_QUOTES),
            mb_strlen($value),
            GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:wizard.chars'
            )
        );

        // Include an ES module in the backend
        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create('@clickstorm/cs-seo/FormEngine/Wizard/CharCounter.js');

        return $result;
    }
}
