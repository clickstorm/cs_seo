/**
 * Module: TYPO3/CMS/CsSeo/FormEngine/Element/JsonLdElement
 * Logic for Preview of Google Rich Results
 *
 * @author Marc Hirdes
 * @version 3.0
 * @license clickstorm GmbH
 */
define(['jquery'], function($) {
  var JsonLdElement = {};

  JsonLdElement.initialize = function() {
    $(document).ready(function() {
      let $checkTextButton = $('.t3js-form-field-json-ld-check');
      let $checkUrlButton = $('.t3js-form-field-json-ld-url');
      let $previewUrlButton = $('.t3js-editform-view');
      let $panel = $checkTextButton.closest('fieldset');
      let $inputJsonLdHR = $panel.find('[data-formengine-input-name$="[tx_csseo_json_ld]"], textarea[name$="[tx_csseo_json_ld]"]');

      // create and submit a form to Google
      $checkTextButton.off().click(function() {
        let $jsonLdInput = $('.js-txcsseo-json-ld-code');
        let $form;

        if($jsonLdInput.length === 0) {
          $form = $('<form style="display:none" method="POST" target="_blank" action="https://search.google.com/test/rich-results"><textarea name="code_snippet" class="js-txcsseo-json-ld-code"></textarea><button type="submit"></button></form>');
          $form.appendTo('.t3js-module-body');
          $jsonLdInput = $form.find('.js-txcsseo-json-ld-code');
        } else {
          $form = $jsonLdInput.closest('form');
        }

        $jsonLdInput.val('<script type="application/ld+json">' + $inputJsonLdHR.val() + '</script>');

        $form.submit();

        return false;
      });

      // add preview url to button
      if($previewUrlButton.length && $previewUrlButton.attr('href')) {
        let checkUrl = $checkUrlButton.attr('href');
        checkUrl += '?url=' + $previewUrlButton.attr('href');
        $checkUrlButton.attr('href', checkUrl);
      }
    });
  }

  return JsonLdElement;
});
