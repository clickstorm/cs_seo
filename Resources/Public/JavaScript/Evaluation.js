/**
 * Google Preview
 *
 * @author Marc Hirdes
 * @version 1.0
 * @license clickstorm GmbH
 */
export const Evaluation = {};

Evaluation.init = function() {
  // evaluation update
  document.getElementById('cs-seo-evaluate').addEventListener('click', function() {
    const evaluateButton = document.getElementById('cs-seo-evaluate');
    const uid = evaluateButton.getAttribute('data-uid');
    const table = evaluateButton.getAttribute('data-table');

    const waitParagraph = document.createElement('p');
    waitParagraph.classList.add('cs-wait');
    waitParagraph.textContent = '...';
    evaluateButton.insertAdjacentElement('beforebegin', waitParagraph);

    fetch(TYPO3.settings.ajaxUrls['tx_csseo_evaluate'], {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ uid: uid, table: table })
    })
      .then(response => response.text())
      .then(responseText => {
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
        evaluateButton.style.display = 'block';
        location.reload();
      });

    evaluateButton.style.display = 'none';
    return false;
  });

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
    // Initialize your select2 replacement here
    // Example using a simple native implementation
    recordSelector.querySelectorAll('option[value=""]:not(:selected)').forEach(option => {
      option.remove();
    });
  }
};

// Call the init function when document is ready
document.addEventListener('DOMContentLoaded', () => {
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
