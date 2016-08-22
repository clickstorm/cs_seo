/**
 * Google Preview
 *
 * @author Marc Hirdes
 * @version 1.0
 * @license clickstorm GmbH
 */
(function ($) {
    $(document).ready(function(){
        $('#cs-seo-evaluate').click(function(key, value) {
            var $this = $(this);
            $('#cs-seo-evaluate').before('<p class="cs-wait">...</p>');
            $.post(
                TYPO3.settings.ajaxUrls['CsSeo::evaluate'],
                {
                    uid: $this.data('uid')
                }
                ).success(function(response){
                    if(top.TYPO3.Notification) {
                        top.TYPO3.Notification.success('Updated', '', 3);
                    } else {
                        top.TYPO3.Flashmessage.display(2, 'Updated', '', 3);
                    }

                location.reload();
            });
            $('#cs-seo-evaluate').remove();
            return false;
        });

    });
})(TYPO3.jQuery || jQuery);