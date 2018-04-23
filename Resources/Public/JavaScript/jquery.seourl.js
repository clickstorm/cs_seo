/*
 * jQuery SeoURL plugin 0.11
 *
 * http://www.spidersoft.com.au/projects/jquery-seo-url-plugin/
 *
 * Copyright (c) 2013 Slawomir Jasinski
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 * 
 */

(function( $ ){

    // prototype, and return string
    String.prototype.seoURL = function (options) {

        // Create some defaults, extending them with any options that were provided
        var settings = $.extend( {
            'transliterate': true,
            'lowercase': false,
            'uppercase': false,
            'divider': '-',
            'append': ''
        }, options);


        var text = this;

        // transliterate
        if (settings.transliterate === true) {
            text = trans(text);
        }
        // lowercase
        if (settings.lowercase === true) {
            text = text.toLowerCase();
        }
        // uppercase
        if (settings.uppercase === true) {
            text = text.toUpperCase();
        }

        text = text.replace(/^\s+|\s+$/g, "") // trim leading and trailing spaces
            .replace(/[_|\s]+/g, "-") // change all spaces and underscores to a hyphen
            .replace(/[^a-zA-z\u0400-\u04FF0-9-]+/g, "") // remove almoust all characters except hyphen
            .replace(/[-]+/g, "-") // replace multiple hyphens
            .replace(/^-+|-+$/g, "") // trim leading and trailing hyphen
            .replace(/[-]+/g, settings.divider)	// replace hyphen with divider
        return text + settings.append;
    }


    var text = '';

    function trans (text) {
        text = strtr(text, special);
        text = strtr(text, from, to)
        return text;
    }

    var from = 'ąĄęĘóÓśŚłŁżŻźŹćĆńŃ' + // polish
        'čďňřšť' + // czech
        'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'; // common

    var to   = 'aAeEoOsSlLzZzZcCnN' +
        'cdnrst' +
        'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy';

    var special = {'ä': 'ae', 'Ä': 'AE', 'ö': 'oe', 'Ö': 'OE', 'ü' : 'ue', 'Ü': 'UE', 'ß':'ss', 'ẞ': 'SS'}


    // from - http://phpjs.org/functions/strtr/
    function strtr (str, from, to) {

        var fr = '',
            i = 0,
            j = 0,
            lenStr = 0,
            lenFrom = 0,
            tmpStrictForIn = false,
            fromTypeStr = '',
            toTypeStr = '',
            istr = '';
        var tmpFrom = [];
        var tmpTo = [];
        var ret = '';
        var match = false;

        // Received replace_pairs?
        // Convert to normal from->to chars
        if (typeof from === 'object') {
            tmpStrictForIn = ini_set('phpjs.strictForIn', false); // Not thread-safe; temporarily set to true
            from = krsort(from);
            ini_set('phpjs.strictForIn', tmpStrictForIn);

            for (fr in from) {
                if (from.hasOwnProperty(fr)) {
                    tmpFrom.push(fr);
                    tmpTo.push(from[fr]);
                }
            }

            from = tmpFrom;
            to = tmpTo;
        }

        // Walk through subject and replace chars when needed
        lenStr = str.length;
        lenFrom = from.length;
        fromTypeStr = typeof from === 'string';
        toTypeStr = typeof to === 'string';

        for (i = 0; i < lenStr; i++) {
            match = false;
            if (fromTypeStr) {
                istr = str.charAt(i);
                for (j = 0; j < lenFrom; j++) {
                    if (istr == from.charAt(j)) {
                        match = true;
                        break;
                    }
                }
            } else {
                for (j = 0; j < lenFrom; j++) {
                    if (str.substr(i, from[j].length) == from[j]) {
                        match = true;
                        // Fast forward
                        i = (i + from[j].length) - 1;
                        break;
                    }
                }
            }
            if (match) {
                ret += toTypeStr ? to.charAt(j) : to[j];
            } else {
                ret += str.charAt(i);
            }
        }

        return ret;
    }

    function krsort (inputArr, sort_flags) {
        // http://kevin.vanzonneveld.net
        // +   original by: GeekFG (http://geekfg.blogspot.com)
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: Brett Zamir (http://brett-zamir.me)
        // %          note 1: The examples are correct, this is a new way
        // %        note 2: This function deviates from PHP in returning a copy of the array instead
        // %        note 2: of acting by reference and returning true; this was necessary because
        // %        note 2: IE does not allow deleting and re-adding of properties without caching
        // %        note 2: of property position; you can set the ini of "phpjs.strictForIn" to true to
        // %        note 2: get the PHP behavior, but use this only if you are in an environment
        // %        note 2: such as Firefox extensions where for-in iteration order is fixed and true
        // %        note 2: property deletion is supported. Note that we intend to implement the PHP
        // %        note 2: behavior by default if IE ever does allow it; only gives shallow copy since
        // %        note 2: is by reference in PHP anyways
        // %        note 3: Since JS objects' keys are always strings, and (the
        // %        note 3: default) SORT_REGULAR flag distinguishes by key type,
        // %        note 3: if the content is a numeric string, we treat the
        // %        note 3: "original type" as numeric.
        // -    depends on: i18n_loc_get_default
        // *     example 1: data = {d: 'lemon', a: 'orange', b: 'banana', c: 'apple'};
        // *     example 1: data = krsort(data);
        // *     results 1: {d: 'lemon', c: 'apple', b: 'banana', a: 'orange'}
        // *     example 2: ini_set('phpjs.strictForIn', true);
        // *     example 2: data = {2: 'van', 3: 'Zonneveld', 1: 'Kevin'};
        // *     example 2: krsort(data);
        // *     results 2: data == {3: 'Kevin', 2: 'van', 1: 'Zonneveld'}
        // *     returns 2: true
        var tmp_arr = {},
            keys = [],
            sorter, i, k, that = this,
            strictForIn = false,
            populateArr = {};

        switch (sort_flags) {
            case 'SORT_STRING':
                // compare items as strings
                sorter = function (a, b) {
                    return that.strnatcmp(b, a);
                };
                break;
            case 'SORT_LOCALE_STRING':
                // compare items as strings, based on the current locale (set with  i18n_loc_set_default() as of PHP6)
                var loc = this.i18n_loc_get_default();
                sorter = this.php_js.i18nLocales[loc].sorting;
                break;
            case 'SORT_NUMERIC':
                // compare items numerically
                sorter = function (a, b) {
                    return (b - a);
                };
                break;
            case 'SORT_REGULAR':
            // compare items normally (don't change types)
            default:
                sorter = function (b, a) {
                    var aFloat = parseFloat(a),
                        bFloat = parseFloat(b),
                        aNumeric = aFloat + '' === a,
                        bNumeric = bFloat + '' === b;
                    if (aNumeric && bNumeric) {
                        return aFloat > bFloat ? 1 : aFloat < bFloat ? -1 : 0;
                    } else if (aNumeric && !bNumeric) {
                        return 1;
                    } else if (!aNumeric && bNumeric) {
                        return -1;
                    }
                    return a > b ? 1 : a < b ? -1 : 0;
                };
                break;
        }

        // Make a list of key names
        for (k in inputArr) {
            if (inputArr.hasOwnProperty(k)) {
                keys.push(k);
            }
        }
        keys.sort(sorter);

        // BEGIN REDUNDANT
        this.php_js = this.php_js || {};
        this.php_js.ini = this.php_js.ini || {};
        // END REDUNDANT
        strictForIn = this.php_js.ini['phpjs.strictForIn'] && this.php_js.ini['phpjs.strictForIn'].local_value && this.php_js.ini['phpjs.strictForIn'].local_value !== 'off';
        populateArr = strictForIn ? inputArr : populateArr;


        // Rebuild array with sorted key names
        for (i = 0; i < keys.length; i++) {
            k = keys[i];
            tmp_arr[k] = inputArr[k];
            if (strictForIn) {
                delete inputArr[k];
            }
        }
        for (i in tmp_arr) {
            if (tmp_arr.hasOwnProperty(i)) {
                populateArr[i] = tmp_arr[i];
            }
        }

        return strictForIn || populateArr;
    }

    function ini_set (varname, newvalue) {
        // http://kevin.vanzonneveld.net
        // +   original by: Brett Zamir (http://brett-zamir.me)
        // %        note 1: This will not set a global_value or access level for the ini item
        // *     example 1: ini_set('date.timezone', 'America/Chicago');
        // *     returns 1: 'Asia/Hong_Kong'

        var oldval = '',
            that = this;
        this.php_js = this.php_js || {};
        this.php_js.ini = this.php_js.ini || {};
        this.php_js.ini[varname] = this.php_js.ini[varname] || {};
        oldval = this.php_js.ini[varname].local_value;

        var _setArr = function (oldval) { // Although these are set individually, they are all accumulated
            if (typeof oldval === 'undefined') {
                that.php_js.ini[varname].local_value = [];
            }
            that.php_js.ini[varname].local_value.push(newvalue);
        };

        switch (varname) {
            case 'extension':
                if (typeof this.dl === 'function') {
                    this.dl(newvalue); // This function is only experimental in php.js
                }
                _setArr(oldval, newvalue);
                break;
            default:
                this.php_js.ini[varname].local_value = newvalue;
                break;
        }
        return oldval;
    }

})((TYPO3.jQuery || jQuery || $));