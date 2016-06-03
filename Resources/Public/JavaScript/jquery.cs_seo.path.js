/**
 * Google Preview
 *
 * @author Marc Hirdes
 * @version 1.0
 * @license clickstorm GmbH
 */
(function ($, TYPO3) {
    $(document).ready(function(){
        var $title = $('.js-cs-seo-title'),
            $inputSeoTitleHR = $('input[data-formengine-input-name$="[tx_csseo_title]"], input[name$="[tx_csseo_title]_hr"]'),
            $inputPageTitleHR = $('input[data-formengine-input-name$="[title]"], input[name$="[title]_hr"]'),
            $inputPathSegmentHR = $('input[data-formengine-input-name$="[tx_realurl_pathsegment]"], input[name$="[tx_realurl_pathsegment]_hr"]'),
            $inputPathSegment = $('input[name$="[tx_realurl_pathsegment]"]'),
            separator = $title.data('separator') ? $title.data('separator') : '',
            pageUid = $('input[name="popViewId"]').val();

        var seoURLoptions = {
            'translitarate': true,
            'uppercase': false,
            "lowercase": true,
            "divider": '-'
        };

        // check if path segment is empty or is siteroot
        if($inputPathSegment.val() == '') {
            var initialTitle = $('input[name$="[title]"]').val();
            if(initialTitle == '' || initialTitle == '[Default Title]') {
                $inputPageTitleHR.on('keyup.csseopath', function() {
                    updatePathSegment($inputPageTitleHR.val());
                });
                $inputPageTitleHR.on('change.csseopath', function() {
                    pathHasChanged();
                });
            } else {
                updatePathSegment(initialTitle);
                pathHasChanged();
            }
        }

        /**
         * update form path inputs
         * @param text
         */
        function updatePathSegment(text) {
            var path = text.seoURL(seoURLoptions);
            $inputPathSegment.val(path);
            $inputPathSegmentHR.val(path);
        }

        /**
         * if path changed via js, this function has to be called to permit the changes
         */
        function pathHasChanged() {
            if (typeof TYPO3.FormEngine !== 'undefined' && typeof TYPO3.FormEngine.Validation !== 'undefined' && typeof TYPO3.FormEngine.Validation.validate === 'function') {
                TBE_EDITOR.fieldChanged('pages', pageUid, 'tx_realurl_pathsegment', 'data[pages][' + pageUid + '][tx_realurl_pathsegment]');
                TYPO3.FormEngine.Validation.validate();
            } else {
                setTimeout(function(){
                    pathHasChanged();
                }, 1000);
            }
        }

    });
})(TYPO3.jQuery, TYPO3);