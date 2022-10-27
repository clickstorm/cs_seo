class JsonLdElement {
  constructor() {
    let checkTextButton = document.querySelector(
        '.t3js-form-field-json-ld-check');
    let checkUrlButton = document.querySelector(
        '.t3js-form-field-json-ld-url');
    let previewUrlButton = document.querySelector('.t3js-editform-view');
    let panel = checkTextButton.closest('fieldset');
    let inputJsonLdHR = panel.querySelector(
        '[data-formengine-input-name$="[tx_csseo_json_ld]"], [data-formengine-input-name$="[json_ld]"], textarea[name$="[tx_csseo_json_ld]"], textarea[name$="[json_ld]"]');

    //create and submit a form to Google
    //Remove all EventListener with a fresh element
    checkTextButton.addEventListener('click', () => {
      let jsonLdInput = document.querySelector('.js-txcsseo-json-ld-code');
      let form;

      if (jsonLdInput === null) {
        let placeholder = document.createElement('div');
        placeholder.innerHTML =
            '<form style="display:none" method="POST" target="_blank" action="https://search.google.com/test/rich-results"><textarea name="code_snippet" class="js-txcsseo-json-ld-code"></textarea><button type="submit"></button></form>';
        form = placeholder.firstElementChild;
        document.querySelector('.t3js-module-body').appendChild(form);
        jsonLdInput = form.querySelectorAll('.js-txcsseo-json-ld-code');
      } else {
        form = jsonLdInput.closest('form');
      }

      jsonLdInput.value = '<script type="application/ld+json">' +
          inputJsonLdHR.value + '</script>';

      form.submit();

      return false;
    });

    // add preview url to button
    if (previewUrlButton !== null && previewUrlButton.getAttribute('href')) {
      let checkUrl = checkUrlButton.getAttribute('href');
      checkUrl += '?url=' + previewUrlButton.getAttribute('href');
      checkUrlButton.setAttribute('href', checkUrl);
    }
  }
}

export default new JsonLdElement;