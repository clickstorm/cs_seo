/**
 * Google Preview
 *
 * @author Marc Hirdes
 * @version 1.0
 * @license clickstorm GmbH
 */
(function ($) {
    $(document).ready(function(){
        var $title = $('.js-cs-seo-title'),
            $desc = $('.js-cs-seo-desc'),
            $panel = $title.closest('fieldset'),
            $inputSeoTitleHR = $panel.find('input[data-formengine-input-name$="[tx_csseo_title]"], input[name$="[tx_csseo_title]_hr"]'),
            $inputPageTitleHR = $('input[data-formengine-input-name$="[title]"], input[name$="[title]_hr"]'),
            $checkboxTitleOnlyHR = $panel.find('input[data-formengine-input-name$="[tx_csseo_title_only]"], input[name$="[tx_csseo_title_only]_0"]'),
            separator = $title.data('separator') ? $title.data('separator') : '',
            siteTitle = $title.data('sitetitle') ? $title.data('sitetitle') : '',
            titleOnly = $checkboxTitleOnlyHR.is(":checked");

        if($panel.find('input[data-formengine-input-name^="data[tx_csseo_domain_model_meta]"]')) {
            $inputSeoTitleHR = $panel.find('input[data-formengine-input-name$="[title]"], input[name$="[title]_hr"]');
            $inputPageTitleHR = $inputSeoTitleHR;
            $checkboxTitleOnlyHR = $panel.find('input[data-formengine-input-name$="[title_only]"], input[name$="[title_only]_0"]');
        }

        $inputSeoTitleHR.on('keyup.csseotitle', function() {
            updateTitle();
        });

        // title change
        $inputPageTitleHR.change(function() {
            updateTitle();
        });

        // title only changes
        $checkboxTitleOnlyHR.change(function() {
            titleOnly = $checkboxTitleOnlyHR.is(":checked");
            updateTitle();
        });

        // description changes
        $panel.find('[data-formengine-input-name$="[description]"], textarea[name$="[description]"]').on('keyup.csseodesc', function() {
            var metaDesc = $(this).val();
            $desc.text(metaDesc);
            $('.js-cs-seo-hidden').toggle(!metaDesc);
        });

        /**
         * Update the title in the preview
         */
        function updateTitle() {
            $title.text(getSeoTitle());
        }

        /**
         *
         * @returns {string}
         */
        function getSeoTitle() {
            var title = ($inputSeoTitleHR.val() != '') ? $inputSeoTitleHR.val() : $inputPageTitleHR.val();
            if (!titleOnly) {
                if ($title.data('first')) {
                    title += separator + siteTitle;
                } else {
                    title = siteTitle + separator + title;
                }
            }
            return title;
        }

    });
})(TYPO3.jQuery);