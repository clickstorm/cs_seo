class SnippetPreview {
  constructor() {
    let previewTitleEl = document.querySelector('.js-cs-seo-title');
    let previewDescriptionEl = document.querySelector('.js-cs-seo-desc');
    let fieldsetEl = previewTitleEl.closest('fieldset');
    let separator = previewTitleEl.dataset.separator ?
        previewTitleEl.dataset.separator :
        '';
    let siteTitle = previewTitleEl.dataset.sitetitle ?
        previewTitleEl.dataset.sitetitle :
        '';
    let fallbackTable = previewTitleEl.dataset.fallbackTable;
    let inputFallbackTitleEl = SnippetPreview.findInputFallback('title',
        previewTitleEl);
    let inputFallbackDescriptionEl = SnippetPreview.findInputFallback(
        'description', previewTitleEl);
    let inputSeoDescriptionEl = fieldsetEl.querySelector(
        '[data-formengine-input-name$="[description]"]');
    let inputSeoTitleEl;
    let checkboxTitleOnlyEl;

    if (fallbackTable === 'pages') {
      inputSeoTitleEl = fieldsetEl.querySelector(
          'input[data-formengine-input-name$="[seo_title]"]');
      checkboxTitleOnlyEl = fieldsetEl.querySelector(
          'input[data-formengine-input-name$="[tx_csseo_title_only]"]');
    } else {
      inputSeoTitleEl = fieldsetEl.querySelector(
          'input[data-formengine-input-name$="[title]"]');
      checkboxTitleOnlyEl = fieldsetEl.querySelector(
          'input[data-formengine-input-name$="[title_only]"]');
    }

    let titleOnly = checkboxTitleOnlyEl.checked; // return boolean

    inputSeoTitleEl.addEventListener('keyup', () => {
      SnippetPreview.updateTitle(inputSeoTitleEl, inputFallbackTitleEl,
          titleOnly, previewTitleEl, separator, siteTitle);
    });

    // title only changes
    checkboxTitleOnlyEl.addEventListener('change', () => {
      titleOnly = checkboxTitleOnlyEl.checked;
      SnippetPreview.updateTitle(inputSeoTitleEl, inputFallbackTitleEl,
          titleOnly, previewTitleEl, separator, siteTitle);
    });

    // description changes
    inputSeoDescriptionEl.addEventListener('keyup', () => {
      SnippetPreview.updateDescription(inputSeoDescriptionEl,
          inputFallbackDescriptionEl, previewDescriptionEl);
    });

    // title fallback change
    if (inputFallbackTitleEl !== null) {
      inputFallbackTitleEl.addEventListener('change', () => {
        SnippetPreview.updateTitle(inputSeoTitleEl, inputFallbackTitleEl,
            titleOnly, previewTitleEl, separator, siteTitle);
      });
    }

    // fallback description changes
    if (inputFallbackDescriptionEl !== null) {
      inputFallbackDescriptionEl.addEventListener('change', () => {
        SnippetPreview.updateDescription(inputSeoDescriptionEl,
            inputFallbackDescriptionEl, previewDescriptionEl);
      });
    }
  }

  static findInputFallback(fieldname, title) {
    let name = '[' + title.dataset.fallbackTable + '][' +
        title.dataset.fallbackUid + '][' +
        title.getAttribute('data-fallback-' + fieldname) + ']';
    return document.querySelector(
        'input[data-formengine-input-name$="' + name + '"],' +
        'textarea[data-formengine-input-name$="' + name + '"]');

  }

  static updateTitle(
      inputSeoTitleEL, inputFallbackTitleEl, titleOnly, titleEl, separator,
      siteTitle) {
    return document.querySelector(
        '.js-cs-seo-title').innerHTML = SnippetPreview.getSeoTitle(
        inputSeoTitleEL, inputFallbackTitleEl, titleOnly, titleEl, separator,
        siteTitle);
  }

  static updateDescription(
      inputSeoDescriptionEl, inputFallbackDescriptionEl, desc) {
    let metaDesc = inputSeoDescriptionEl.value;
    if (metaDesc === '' && inputFallbackDescriptionEl !== null) {
      metaDesc = inputFallbackDescriptionEl.value;
    }
    desc.innerHTML = metaDesc;

    document.querySelector('.js-cs-seo-hidden').style.display = metaDesc ?
        'none' :
        '';
    return desc;
  }

  static getSeoTitle(
      inputSeoTitleEl, inputFallbackTitleEl, titleOnly, titleEl, separator,
      siteTitle) {

    let title = inputSeoTitleEl.value;

    if (title === '' && inputFallbackTitleEl !== null) {
      title = inputFallbackTitleEl.value;
    }

    if(title === '' && titleEl.dataset.fallbackTitleValue !== null) {
      title = titleEl.dataset.fallbackTitleValue;
    }

    if (!titleOnly) {
      if (titleEl.dataset.first) {
        title += separator + siteTitle;
      } else {
        title = siteTitle + separator + title;
      }
    }

    return title;
  }
}

export default new SnippetPreview;
