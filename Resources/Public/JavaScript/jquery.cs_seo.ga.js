
/**
 * Google Preview
 *
 * @author Ryan Chase
 * @version 1.0
 * @url http://www.blastam.com/blog/index.php/2013/03/how-to-track-downloads-in-google-analytics-v2
 */
(function ($, document) {
    $(document).ready(function(){
        var filetypes = /\.(zip|exe|dmg|pdf|doc.*|xls.*|ppt.*|mp3|txt|rar|wma|mov|avi|wmv|flv|wav)$/i;

        $('a').on('click', function (event) {
            var el = $(this),
                track = true,
                href = this.href,
                isThisDomain = href.match(document.domain.split('.').reverse()[1] + '.' + document.domain.split('.').reverse()[0]);

            if (!href.match(/^javascript:/i)) {
                var elEv = [];
                elEv.value = 0, elEv.non_i = false;
                if (href.match(/^mailto\:/i)) {
                    elEv.category = "email";
                    elEv.action = "click";
                    elEv.label = href.replace(/^mailto\:/i, '');
                    elEv.loc = href;
                }
                else if (href.match(filetypes)) {
                    var extension = (/[.]/.exec(href)) ? /[^.]+$/.exec(href) : undefined;
                    elEv.category = "download";
                    elEv.action = "click-" + extension[0];
                    elEv.label = href.replace(/ /g, "-");
                    elEv.loc = href;
                }
                else if (href.match(/^https?\:/i) && !isThisDomain) {
                    elEv.category = "external";
                    elEv.action = "click";
                    elEv.label = href.replace(/^https?\:\/\//i, '');
                    elEv.non_i = true;
                    elEv.loc = href;
                }
                else if (href.match(/^tel\:/i)) {
                    elEv.category = "telephone";
                    elEv.action = "click";
                    elEv.label = href.replace(/^tel\:/i, '');
                    elEv.loc = href;
                }
                else track = false;

                if (track) {
                    ga('send', 'event', elEv.category.toLowerCase(), elEv.action.toLowerCase(), elEv.label.toLowerCase(), elEv.value, {'nonInteraction': elEv.non_i});
                }
            }
        });
    });
})($, document);
