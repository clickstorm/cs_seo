/**
 * Module: TYPO3/CMS/CsSeo/FormEngine/Element/SnippetPreview
 * Logic for Google SERP
 *
 * @author Marc Hirdes
 * @version 3.0
 * @license clickstorm GmbH
 */
define(['jquery'], function($) {
    var SnippetPreview = {};

	SnippetPreview.initialize = function() {
		$(document).ready(function() {
			var $title = $('.js-cs-seo-title'),
				$desc = $('.js-cs-seo-desc'),
				$panel = $title.closest('fieldset'),
				separator = $title.data('separator') ? $title.data('separator') : '',
				siteTitle = $title.data('sitetitle') ? $title.data('sitetitle') : '',
				fallbackTable = $title.data('fallback-table'),
				$inputFallbackTitleHR = findInputFallback('title'),
				$inputFallbackDescriptionHR = findInputFallback('description'),
				$inputSeoDescriptionHR = $panel.find('[data-formengine-input-name$="[description]"], textarea[name$="[description]"]');
			if(fallbackTable == 'pages') {
				var $inputSeoTitleHR = $panel.find('input[data-formengine-input-name$="[tx_csseo_title]"], input[name$="[tx_csseo_title]_hr"]'),
					$checkboxTitleOnlyHR = $panel.find('input[data-formengine-input-name$="[tx_csseo_title_only]"], input[name$="[tx_csseo_title_only]_0"]');
			} else {
				var $inputSeoTitleHR = $panel.find('input[data-formengine-input-name$="[title]"], input[name$="[title]_hr"]'),
					$checkboxTitleOnlyHR = $panel.find('input[data-formengine-input-name$="[title_only]"], input[name$="[title_only]_0"]');
			}

			var titleOnly = $checkboxTitleOnlyHR.is(":checked");

			$inputSeoTitleHR.on('keyup.csseotitle', function() {
				console.log('up');
				updateTitle();
			});

			// title only changes
			$checkboxTitleOnlyHR.change(function() {
				titleOnly = $checkboxTitleOnlyHR.is(":checked");
				updateTitle();
			});

			// description changes
			$inputSeoDescriptionHR.on('keyup.csseodesc', function() {
				updateDescription();
			});

			// title change
			$inputFallbackTitleHR.change(function() {
				updateTitle();
			});

			// fallback description changes
			$inputFallbackDescriptionHR.change(function() {
				updateDescription();
			});

			function findInputFallback(fieldname) {
				var name = '[' + $title.data('fallback-table') + '][' + $title.data('fallback-uid') + '][' + $title.data('fallback-' + fieldname) + ']';
				return $('input[data-formengine-input-name$="' + name + '"],' +
					'input[name$="' + name + '_hr"],' +
					'textarea[data-formengine-input-name$="' + name + '"],' +
					'textarea[name$="' + name + '"]');
			}

			/**
			 * Update the title in the preview
			 */
			function updateTitle() {
				$title.text(getSeoTitle());
			}

			/**
			 * Update the title in the preview
			 */
			function updateDescription() {
				var metaDesc = $inputSeoDescriptionHR.val();
				if(metaDesc == '' && $inputFallbackDescriptionHR.length > 0) {
					metaDesc = $inputFallbackDescriptionHR.val();
				}
				$desc.text(metaDesc);
				$('.js-cs-seo-hidden').toggle(!metaDesc);
			}

			/**
			 *
			 * @returns {string}
			 */
			function getSeoTitle() {
				var title = $inputSeoTitleHR.val();
				if(title == '' && $inputFallbackTitleHR.length > 0) {
					title = $inputFallbackTitleHR.val();
				}
				if(!titleOnly) {
					if($title.data('first')) {
						title += separator + siteTitle;
					} else {
						title = siteTitle + separator + title;
					}
				}
				return title;
			}
		});
	}

	return SnippetPreview;
});