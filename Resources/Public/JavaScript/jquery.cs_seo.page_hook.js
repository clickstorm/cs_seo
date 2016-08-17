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
            $('#cs-seo-evaluate').before('<p id="cs-wait">...</p>');
            $.post(
                TYPO3.settings.ajaxUrls['CsSeo::evaluate'],
                {
                    uid: $this.data('uid')
                }
                ).success(function(response){
                top.TYPO3.Notification.success('Updated', '', 1000);
                location.reload();
            });
            $('#cs-seo-evaluate').remove();
            return false;
        });

    });
})(TYPO3.jQuery);