/**
 * Google Preview
 *
 * @author Marc Hirdes
 * @version 1.0
 * @license clickstorm GmbH
 */
import DocumentService from"@typo3/core/document-service.js";
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
export const Evaluation = {};

Evaluation.init = function() {
  // evaluation update
  const evaluateButton = document.getElementById('cs-seo-evaluate');
  if (evaluateButton) {
    evaluateButton.addEventListener('click', function() {
      const uid = evaluateButton.getAttribute('data-uid');
      const table = evaluateButton.getAttribute('data-table') ? evaluateButton.getAttribute('data-table') : 'pages';

      const waitParagraph = document.createElement('p');
      waitParagraph.classList.add('cs-wait');
      waitParagraph.textContent = '...';
      evaluateButton.insertAdjacentElement('beforebegin', waitParagraph);

      let request = new AjaxRequest(TYPO3.settings.ajaxUrls.tx_csseo_evaluate)

      const json = { uid: uid, table: table };
      let promise = request.post(json);

      promise.then(async function (response) {
        const responseText = await response.resolve();
        if (responseText.length > 0) {
          const messageElement = new DOMParser().parseFromString(responseText, 'text/html').querySelector('.alert, .message-body');
          const message = messageElement ? messageElement.textContent : '';

          if (top.TYPO3.Notification) {
            top.TYPO3.Notification.error('Not Updated', message, 5);
          } else {
            top.TYPO3.Flashmessage.display(4, 'Not Updated', message);
          }
        } else {
          if (top.TYPO3.Notification) {
            top.TYPO3.Notification.success('Updated', '', 3);
          } else {
            top.TYPO3.Flashmessage.display(2, 'Updated', '', 3);
          }
        }
        document.querySelector('.cs-wait').remove();
        evaluateButton.classList.remove('hidden');
        location.reload();
      });

      evaluateButton.classList.add('hidden');
      return false;
    });
  }

  // toggle accordion
  const toggles = document.querySelectorAll('.js-csseo-toggle');
  if (toggles.length > 0) {
    toggles.forEach(toggle => {
      const content = document.querySelector(toggle.getAttribute('data-content'));
      let showResults = false;
      const useCookies = toggle.getAttribute('data-cookie') === 'true';

      if (useCookies) {
        showResults = getCookie('seo-results') === '1';
      }

      function toggleResults() {
        content.style.display = showResults ? 'block' : 'none';
        toggle.classList.toggle('csseo-icon-up-open', showResults);
        toggle.classList.toggle('csseo-icon-down-open', !showResults);
      }

      toggle.addEventListener('click', () => {
        showResults = !showResults;
        toggleResults();
        if (useCookies) {
          setCookie('seo-results', showResults ? '1' : '0');
        }
      });

      if (!showResults) {
        toggleResults();
      }
    });
  }

  // record selector with search box
  const recordSelector = document.getElementById('cs-record');
  if (recordSelector) {
    recordSelector.querySelectorAll('option[value=""]').forEach(option => {
      if (!option.selected) {
        option.remove();
      }
    });
  }
};

// Call the init function when document is ready
DocumentService.ready().then(() => {
  Evaluation.init();
});

// Utility functions for cookies
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}

function setCookie(name, value, days = 365) {
  const date = new Date();
  date.setTime(date.getTime() + (days*24*60*60*1000));
  const expires = `expires=${date.toUTCString()}`;
  document.cookie = `${name}=${value}; ${expires}; path=/`;
}

// Exporting Evaluation for other modules
export default Evaluation;
