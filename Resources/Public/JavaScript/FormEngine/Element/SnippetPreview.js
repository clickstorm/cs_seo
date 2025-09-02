/* --- SEO Snippet Preview + Meter (encapsulated helpers, responsive, accessible) ---
 * Desktop:
 *  - Single-line title (JS ellipsis), overflow if single-line width > container width
 * Mobile (container width <= 600px):
 *  - Up to 2 visual lines via CSS clamp
 *  - Overflow only if text would exceed 2 lines (DOM height check)
 * Meter:
 *  - Mobile bar fill capped to container width; desktop uses a small soft cap
 * Status & warn text are split across two spans.
 */

class SnippetPreview {
  constructor() {
    // Instance-scoped hidden measurers
    this._multiLineBox = null;     // for multi-line overflow checks
    this._singleLineBox = null;    // for single-line width measuring

    const widget = document.querySelector('.js-seo-widget');
    if (!widget) return;

    this.wrapper    = widget.closest('.tx-cs_seo') || widget;
    this.palette   = widget.closest('fieldset');
    this.previewBox = widget.querySelector('.js-seo-preview');
    this.titleEl    = widget.querySelector('.js-seo-title');
    this.descEl     = widget.querySelector('.js-seo-desc');
    if (!this.previewBox || !this.titleEl) return;

    // Dataset from title
    this.separator     = this.titleEl.dataset.separator || '';
    this.siteTitle     = this.titleEl.dataset.sitetitle || '';
    this.fallbackTable = this.titleEl.dataset.fallbackTable;

    // Find inputs globally (TYPO3 markup varies)
    this.inputSeoTitleEl = this.palette.querySelector(
      this.fallbackTable === 'pages'
        ? 'input[data-formengine-input-name$="[seo_title]"]'
        : 'input[data-formengine-input-name$="[title]"]'
    );
    this.checkboxTitleOnlyEl = this.palette.querySelector(
      this.fallbackTable === 'pages'
        ? 'input[data-formengine-input-name$="[tx_csseo_title_only]"]'
        : 'input[data-formengine-input-name$="[title_only]"]'
    );
    this.inputSeoDescriptionEl = this.palette.querySelector('[data-formengine-input-name$="[description]"]');

    this.inputFallbackTitleEl = SnippetPreview._findInputFallback('title', this.titleEl);
    this.inputFallbackDescriptionEl = SnippetPreview._findInputFallback('description', this.titleEl);

    if (!this.inputSeoTitleEl || !this.checkboxTitleOnlyEl || !this.inputSeoDescriptionEl) return;
    this.titleOnly = this.checkboxTitleOnlyEl.checked;

    // Meter config & refs
    const meterEl = widget.querySelector('.js-seo-meter');
    this.meterFill     = widget.querySelector('.js-seo-meter-fill');
    this.meterCountNum = widget.querySelector('.js-seo-meter-count'); // number only
    this.meterStatus   = widget.querySelector('.js-seo-meter-status');
    this.meterWarn     = widget.querySelector('.js-seo-meter-warn');
    this.barEl         = widget.querySelector('.tx-cs_seo-meter__bar');

    this.minChars  = parseInt(meterEl?.dataset.minChars || '35', 10);
    this.maxChars  = parseInt(meterEl?.dataset.maxChars || '60', 10);
    this.warnUnder = meterEl?.dataset.warnUnder || `– below recommendation (${this.minChars})`;
    this.warnOver  = meterEl?.dataset.warnOver  || `– above recommendation (${this.maxChars})`;
    this.warnTrunc = meterEl?.dataset.warnTrunc || '– likely truncated in search results';

    this.labelMeter      = meterEl?.dataset.labelMeter || 'SEO title length';
    this.statusOk        = meterEl?.dataset.labelStatusOk || 'OK';
    this.statusShort     = meterEl?.dataset.labelStatusShort || 'Too short';
    this.statusOverflow  = meterEl?.dataset.labelStatusOverflow || 'Pixel overflow';
    this.statusOverChars = meterEl?.dataset.labelStatusOverchars || 'Above recommendation';

    // Widths + fallback class if container queries are unsupported
    this.supportsContainerQueries = 'containerType' in document.documentElement.style;
    this._computeWidths();

    // Listeners
    this.inputSeoTitleEl.addEventListener('input', () => this._render(), { passive: true });
    this.checkboxTitleOnlyEl.addEventListener('change', () => { this.titleOnly = this.checkboxTitleOnlyEl.checked; this._render(); });
    this.inputSeoDescriptionEl.addEventListener('input', () => {
      SnippetPreview._updateDescription(this.inputSeoDescriptionEl, this.inputFallbackDescriptionEl, this.descEl);
    });
    if (this.inputFallbackTitleEl) this.inputFallbackTitleEl.addEventListener('change', () => this._render());
    if (this.inputFallbackDescriptionEl) {
      this.inputFallbackDescriptionEl.addEventListener('change', () => {
        SnippetPreview._updateDescription(this.inputSeoDescriptionEl, this.inputFallbackDescriptionEl, this.descEl);
      });
    }

    // Responsive
    if (window.ResizeObserver) {
      const ro = new ResizeObserver(() => { this._computeWidths(); this._render(); });
      ro.observe(this.previewBox);
      this._ro = ro;
    } else {
      window.addEventListener('resize', () => { this._computeWidths(); this._render(); }, { passive: true });
    }

    // Initial paint
    this._render();
    SnippetPreview._updateDescription(this.inputSeoDescriptionEl, this.inputFallbackDescriptionEl, this.descEl);
  }

  /* ----------------------
   * Encapsulated utilities
   * ---------------------- */

  // Find fallback input/textarea for a given field (TYPO3 form)
  static _findInputFallback(fieldname, titleEl) {
    if (!titleEl) return null;
    const name = `[${titleEl.dataset.fallbackTable}][${titleEl.dataset.fallbackUid}][${titleEl.getAttribute('data-fallback-' + fieldname)}]`;
    return document.querySelector(
      `input[data-formengine-input-name$="${name}"], textarea[data-formengine-input-name$="${name}"]`
    );
  }

  // Update description preview and placeholder visibility
  static _updateDescription(inputSeoDescriptionEl, inputFallbackDescriptionEl, descEl) {
    let metaDesc = inputSeoDescriptionEl?.value || '';
    if (metaDesc === '' && inputFallbackDescriptionEl) metaDesc = inputFallbackDescriptionEl.value || '';
    if (descEl) descEl.textContent = metaDesc;
    const hidden = document.querySelector('.js-seo-hidden');
    if (hidden) hidden.style.display = metaDesc ? 'none' : '';
    return descEl;
  }

  // Build final SEO title considering "title only" and site title position
  static _composeTitle(inputSeoTitleEl, inputFallbackTitleEl, titleOnly, titleEl, separator, siteTitle) {
    let title = inputSeoTitleEl?.value || '';
    if (title === '' && inputFallbackTitleEl) title = inputFallbackTitleEl.value || '';
    if (title === '' && titleEl?.dataset.fallbackSeoTitleValue !== null) title = titleEl.dataset.fallbackSeoTitleValue || '';
    if (title === '' && titleEl?.dataset.fallbackTitleValue !== null) title = titleEl.dataset.fallbackTitleValue || '';
    if (!titleOnly) {
      if (titleEl?.dataset.first) title += separator + siteTitle;
      else title = siteTitle + separator + title;
    }
    return title;
  }

  // Create/return a hidden block for multi-line measuring
  _getMultiLineBox() {
    if (this._multiLineBox) return this._multiLineBox;
    const box = document.createElement('div');
    const cs = getComputedStyle(this.titleEl);
    box.style.position = 'absolute';
    box.style.left = '-99999px';
    box.style.top = '0';
    box.style.visibility = 'hidden';
    // Typography
    box.style.fontFamily    = cs.fontFamily;
    box.style.fontSize      = cs.fontSize;
    box.style.fontWeight    = cs.fontWeight;
    box.style.lineHeight    = cs.lineHeight;
    box.style.letterSpacing = cs.letterSpacing;
    // Multi-line behavior
    box.style.whiteSpace = 'normal';
    box.style.wordBreak  = 'break-word';
    // No layout side-effects
    box.style.padding = '0';
    box.style.margin  = '0';
    box.style.boxSizing = 'border-box';
    document.body.appendChild(box);
    this._multiLineBox = box;
    return box;
  }

  // Create/return a hidden span for single-line width measuring
  _getSingleLineBox() {
    if (this._singleLineBox) return this._singleLineBox;
    const span = document.createElement('span');
    const cs = getComputedStyle(this.titleEl);
    span.style.position = 'absolute';
    span.style.left = '-99999px';
    span.style.top = '0';
    span.style.visibility = 'hidden';
    // Typography
    span.style.fontFamily    = cs.fontFamily;
    span.style.fontSize      = cs.fontSize;
    span.style.fontWeight    = cs.fontWeight;
    span.style.lineHeight    = cs.lineHeight;
    span.style.letterSpacing = cs.letterSpacing;
    // Force single-line
    span.style.whiteSpace   = 'nowrap';
    span.style.textOverflow = 'clip';
    // No layout effects
    span.style.padding = '0';
    span.style.margin  = '0';
    document.body.appendChild(span);
    this._singleLineBox = span;
    return span;
  }

  // Measure if text would exceed maxLines at given width
  _exceedsLines(text, widthPx, maxLines) {
    const box = this._getMultiLineBox();
    box.style.width = `${Math.max(1, widthPx)}px`;
    box.textContent = text || '';

    const cs = getComputedStyle(this.titleEl);
    const fontSize = parseFloat(cs.fontSize) || 20;
    const lineHeightPx = cs.lineHeight === 'normal'
      ? fontSize * 1.2
      : (parseFloat(cs.lineHeight) || fontSize * 1.2);

    const maxHeight = lineHeightPx * maxLines + 0.5;
    return box.getBoundingClientRect().height > maxHeight;
  }

  // Measure rendered single-line width (CSS px)
  _measureSingleLineWidth(text) {
    const box = this._getSingleLineBox();
    box.textContent = text || '';
    return box.getBoundingClientRect().width;
  }

  // Grapheme-safe ellipsis for single-line desktop preview
  _ellipsizeToWidth(text, maxPx) {
    const fullW = this._measureSingleLineWidth(text);
    if (fullW <= maxPx) return { text, width: fullW };
    const ell = '…';
    const parts = ('Segmenter' in Intl)
      ? Array.from(new Intl.Segmenter(undefined, { granularity: 'grapheme' }).segment(text), s => s.segment)
      : null;
    let lo = 0, hi = parts ? parts.length : text.length, best = '', bestW = 0;
    while (lo <= hi) {
      const mid = (lo + hi) >> 1;
      const candidate = (parts ? parts.slice(0, mid).join('') : text.slice(0, mid)) + ell;
      const w = this._measureSingleLineWidth(candidate);
      if (w <= maxPx) { best = candidate; bestW = w; lo = mid + 1; } else { hi = mid - 1; }
    }
    return { text: best || ell, width: bestW };
  }

  // Compute container widths and toggle fallback class
  _computeWidths() {
    this.maxPx = Math.max(1, this.previewBox.clientWidth || 600);
    this.desktopCapPx = Math.round(this.maxPx + Math.min(40, this.maxPx * 0.08)); // desktop soft cap
    if (this.barEl) this.barEl.setAttribute('aria-valuemax', String(this.maxPx));

    if (!this.supportsContainerQueries) {
      if (this.maxPx < 600) this.wrapper.classList.add('is-narrow');
      else this.wrapper.classList.remove('is-narrow');
    }
  }

  /* ----------------------
   * Rendering
   * ---------------------- */

  _render() {
    // Compose final SEO title string
    const full = SnippetPreview._composeTitle(
      this.inputSeoTitleEl,
      this.inputFallbackTitleEl,
      this.titleOnly,
      this.titleEl,
      this.separator,
      this.siteTitle
    );

    // Mode by container width
    const isMobileLike = (this.maxPx <= 600);

    // Render preview text
    if (this.titleEl) {
      if (isMobileLike) {
        // Show full; CSS clamps to 2 lines
        this.titleEl.textContent = full;
        this.titleEl.title = full;
      } else {
        // Single-line with JS ellipsis
        const cut = this._ellipsizeToWidth(full, this.maxPx);
        this.titleEl.textContent = cut.text;
        this.titleEl.title = full;
      }
    }

    // DOM-accurate single-line width (for bar progress + desktop overflow)
    const rawWidth = this._measureSingleLineWidth(full);

    // Overflow: desktop by single-line width; mobile by >2 lines
    const len = (full || '').length;
    const mobileOverflow  = isMobileLike ? this._exceedsLines(full, this.maxPx, 2) : false;
    const desktopOverflow = !isMobileLike && rawWidth > this.maxPx;
    const pixelOverflow   = isMobileLike ? mobileOverflow : desktopOverflow;

    const tooShort  = len > 0 && len < this.minChars;
    const overChars = len > this.maxChars; // advisory only

    // Meter fill (mobile: strictly cap to container; desktop: soft cap)
    if (this.meterFill && this.barEl) {
      const refWidth = isMobileLike ? this.maxPx : this.desktopCapPx;
      const pct = Math.min(100, Math.round((Math.min(rawWidth, refWidth) / refWidth) * 100));
      this.meterFill.style.width = pct + '%';
      this.meterFill.classList.remove('is-ok', 'is-over');
      if (pixelOverflow) this.meterFill.classList.add('is-over');
      else if (len >= this.minChars) this.meterFill.classList.add('is-ok');

      this.barEl.setAttribute('aria-valuenow', String(Math.min(Math.round(rawWidth), this.maxPx)));
      this.barEl.setAttribute('aria-valuemax', String(this.maxPx));
      this.barEl.setAttribute('aria-label', this.labelMeter);
    }

    // Counter (number only)
    if (this.meterCountNum) this.meterCountNum.textContent = String(len);

    // Status + warn (split)
    if (this.meterStatus) {
      let status = this.statusOk;
      if (pixelOverflow) status = this.statusOverflow;
      else if (tooShort) status = this.statusShort;
      this.meterStatus.textContent = status;
    }
    if (this.meterWarn) {
      let warn = '';
      if (pixelOverflow) warn = this.warnTrunc;
      else if (tooShort) warn = this.warnUnder;
      else if (overChars) warn = this.statusOverChars || this.warnOver;
      this.meterWarn.textContent = warn;
    }
  }
}

/* Instantiate when module loads */
export default new SnippetPreview;
