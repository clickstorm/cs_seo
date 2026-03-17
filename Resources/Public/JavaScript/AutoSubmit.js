import DocumentService from "@typo3/core/document-service.js";

class AutoSubmit {
  constructor() {
    DocumentService.ready().then(() => {
      document.querySelectorAll('.js-csseo-autosubmit').forEach((element) => {
        element.addEventListener('change', () => {
          element.closest('form')?.submit();
        });
      });
    });
  }
}

export default new AutoSubmit;
