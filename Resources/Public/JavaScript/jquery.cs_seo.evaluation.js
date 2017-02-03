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
            var $this = $(this),
                $evaluateButton = $('#cs-seo-evaluate');

	        $evaluateButton.before('<p class="cs-wait">...</p>');
            $.post(
                TYPO3.settings.ajaxUrls['CsSeo::evaluate'],
                {
                    uid: $this.data('uid')
                }
                ).done(function(response, textStatus, jqXHR){
                    if(response.length > 0) {
	                    if(top.TYPO3.Notification) {
		                    var message = $(response).find('.alert').first().text();
		                    top.TYPO3.Notification.error('Not Updated', message, 3);
	                    } else {
		                    var message = $(response).find('.message-body').first().text();
		                    top.TYPO3.Flashmessage.display(4, 'Not Updated', message);
	                    }
                        $('.cs-wait').remove();
	                    $evaluateButton.show();
                    } else {
	                    if(top.TYPO3.Notification) {
		                    top.TYPO3.Notification.success('Updated', '', 3);
	                    } else {
		                    top.TYPO3.Flashmessage.display(2, 'Updated', '', 3);
	                    }
	                    location.reload();
                    }
            });
	        $evaluateButton.hide();
            return false;
        });

        var $toggle = $('#cs-seo-toggle');

        if($toggle.length > 0) {
            var $content = $('.cs-seo-results .results');
            var showResults = $.cookie('seo-results') == 1 ? true : false;

            function toggleResults() {
                $content.toggle(showResults);
                $toggle.toggleClass('csseo-icon-up-open', showResults);
                $toggle.toggleClass('csseo-icon-down-open', !showResults);
            }

            $toggle.click(function() {
                showResults = !showResults;
                toggleResults();
                $.cookie('seo-results', showResults ? 1 : 0);
            });

            if(!showResults) {
                toggleResults();
            }
        }

    });
})(TYPO3.jQuery || jQuery);