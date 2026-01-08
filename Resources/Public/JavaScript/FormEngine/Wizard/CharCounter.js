class CharCounter {
  constructor() {
    document.querySelectorAll('.js-csseo-char-counter').forEach((wrapper) => {
      const input = this.getInput(wrapper);
      if (!input) return;

      const handler = () => this.updateCounter(wrapper);

      input.addEventListener('input', handler);
      input.addEventListener('change', handler);
      input.addEventListener('keyup', handler);
    });
  }

  // Updates a simple character counter for TYPO3 backend fields.
  // Uses event delegation so it also works after dynamic form updates.
  updateCounter(wrapper) {
    const input = this.getInput(wrapper);
    if (!input) return;

    const valueEl = wrapper.querySelector('.js-csseo-char-counter-value');
    if (!valueEl) return;

    const len = [...(input.value || '')].length; // handles surrogate pairs better than .length
    valueEl.textContent = String(len);
  }

  getInput(wrapper) {
    const fieldName = wrapper.dataset.fieldName;
    if (!fieldName) return;

    return document.querySelector(`input[data-formengine-input-name="${fieldName}"]`);
  }
}

export default new CharCounter;
