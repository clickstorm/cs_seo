class CharCounter {
  constructor() {
    document.querySelectorAll('.js-csseo-char-counter').forEach((wrapper) => {
      const input = this.getInput(wrapper);
      if (!input) return;

      const handler = () => this.updateCounter(wrapper);

      input.addEventListener('input', handler);
      input.addEventListener('change', handler);
      input.addEventListener('keyup', handler);
      this.updateCounter(wrapper);
    });
  }

  // Updates a simple character counter for TYPO3 backend fields.
  // Uses event delegation so it also works after dynamic form updates.
  updateCounter(wrapper) {
    const input = this.getInput(wrapper);
    if (!input) return;

    const valueEl = wrapper.querySelector('.js-csseo-char-counter-value');
    const messageEl = wrapper.querySelector('.js-csseo-char-counter-message');
    if (!valueEl || !messageEl) return;

    const len = [...(input.value || '')].length; // handles surrogate pairs better than .length
    valueEl.textContent = String(len);

    const minChars = parseInt(wrapper.dataset.minChars || '0', 10);
    const maxChars = parseInt(wrapper.dataset.maxChars || '0', 10);
    const labelChars = wrapper.dataset.labelChars || 'characters';
    const labelStatusOk = wrapper.dataset.labelStatusOk || 'OK';
    const labelCharsMissing = wrapper.dataset.labelCharsMissing || 'Characters missing';
    const labelCharsOver = wrapper.dataset.labelCharsOver || 'Above recommendation';

    wrapper.classList.remove('badge-info', 'badge-success', 'badge-warning');

    if (!minChars && !maxChars) {
      valueEl.textContent = String(len);
      messageEl.innerHTML = `&nbsp;${labelChars}`;
      wrapper.classList.add('badge-info');
      return;
    }

    if (len === 0) {
      valueEl.textContent = String(len);
      messageEl.innerHTML = `&nbsp;${labelChars}`;
      wrapper.classList.add('badge-info');
      return;
    }

    if (minChars > 0 && len < minChars) {
      valueEl.textContent = '';
      messageEl.textContent = `${labelCharsMissing}: ${Math.max(0, minChars - len)}`;
      wrapper.classList.add('badge-warning');
      return;
    }

    if (maxChars > 0 && len > maxChars) {
      valueEl.textContent = '';
      messageEl.textContent = `${labelCharsOver}: ${Math.max(0, len - maxChars)}`;
      wrapper.classList.add('badge-warning');
      return;
    }

    valueEl.textContent = '';
    messageEl.textContent = labelStatusOk;
    wrapper.classList.add('badge-success');
  }

  getInput(wrapper) {
    const fieldName = wrapper.dataset.fieldName;
    if (!fieldName) return;

    return document.querySelector(
      `input[data-formengine-input-name="${fieldName}"], textarea[data-formengine-input-name="${fieldName}"]`
    );
  }
}

export default new CharCounter;
