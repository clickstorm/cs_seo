/**
 * Google Preview
 *
 * @author Marc Hirdes
 * @version 1.0
 * @license clickstorm GmbH
 */
(function ($) {
    $(document).ready(function(){

        // evaluation update
        $('#cs-seo-evaluate').click(function(key, value) {
            var $this = $(this),
                $evaluateButton = $('#cs-seo-evaluate');

            $evaluateButton.before('<p class="cs-wait">...</p>');
            $.post(
                TYPO3.settings.ajaxUrls['tx_csseo_evaluate'],
                {
                    uid: $this.data('uid'),
                    table: $this.data('table')
                }
                ).done(function(response){
                    if(response.length > 0) {
                        if(top.TYPO3.Notification) {
                            var message = $(response).find('.alert').first().text();
                            top.TYPO3.Notification.error('Not Updated', message, 5);
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

        // toggle accordion
        var $toggles = $('.js-csseo-toggle');
        if($toggles.length > 0) {
            $toggles.each(function() {
	            var $toggle = $(this),
                    $content = $($toggle.data('content')),
		            showResults = false,
		            useCookies = $toggle.data('cookie');

	            if(useCookies) {
		            showResults = $.cookie('seo-results') == 1 ? true : false;
	            }

	            function toggleResults() {
		            $content.toggle(showResults);
		            $toggle.toggleClass('csseo-icon-up-open', showResults);
		            $toggle.toggleClass('csseo-icon-down-open', !showResults);
	            }

	            $toggle.click(function() {
		            showResults = !showResults;
		            toggleResults();
		            if(useCookies) {
			            $.cookie('seo-results', showResults ? 1 : 0);
		            }
	            });

	            if(!showResults) {
		            toggleResults();
	            }
            });
        }

        // record selector with search box
        var $recordSelector = $('#cs-record');
        if($recordSelector.length > 0) {
	        $recordSelector.select2();
	        $recordSelector.find('option[value=""]:not(:selected)').remove();
        }

    });
})(TYPO3.jQuery || jQuery || $);