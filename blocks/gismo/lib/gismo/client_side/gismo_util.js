/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function gismo_util(g) {
    // gismo instance
    this.gismo = g;

    // max/min value of array
    Array.max = function (array) {
        return Math.max.apply(Math, array);
    };
    Array.min = function (array) {
        return Math.min.apply(Math, array);
    };

    // show modal dialog method
    this.show_modal_dialog = function (title, message) {
        var dialog = $("<div></div>").attr("id", "dialog");
        dialog.html(message);
        dialog.attr("title", title);
        dialog.dialog({resizable: false,
            modal: true,
            draggable: false,
            buttons:
                    {
                        'Ok': function () {
                            $(this).dialog('close');
                        }
                    }
        });
    }

    // show confirm dialog
    this.show_exit_confirmation = function (title, message) {
        var dialog = $("<div></div>").attr("id", "dialog");
        dialog.html(message);
        dialog.attr("title", title);
        dialog.dialog({resizable: false,
            modal: true,
            draggable: false,
            buttons:
                    {
                        'No': function () {
                            $(this).dialog('close');
                        },
                        'Yes': function () {
                            $(this).dialog('close');
                            window.close();
                        }
                    }
        });
    }

    this.array_values = function (array) {
        var tmp = [], count = 0, key = '';
        for (key in array) {
            tmp[count] = array[key];
            count++;
        }
        return tmp;
    }

    // associative array length
    this.get_assoc_array_length = function (temp_array) {
        var result = 0;
        for (temp_val in temp_array) {
            result++;
        }
        return result;
    }

    // intelligent substring
    this.intelligent_substring = function (string, include_end, max_len, max_offset) {
        // max len and max offset taken from config
        if (max_len == undefined) {
            max_len = this.gismo.cfg.chart_axis_label_max_len;
        }
        if (max_offset == undefined) {
            max_offset = this.gismo.cfg.chart_axis_label_max_offset;
        }
        var pos, new_string;
        // work on the string
        if (string.length > (max_len + max_offset)) {
            pos = string.indexOf(" ", max_len);
            if (pos != -1 && pos <= max_len + max_offset) {
                new_string = string.substring(0, pos) + " ... ";
            } else {
                new_string = string.substring(0, max_len) + " ... ";
            }
            // include last 4 chars
            if (include_end == true) {
                // -5 => because there is the sequence ' ... '
                var start = (string.length - (new_string.length - 5) >= 4) ? (string.length - 4) : (string.length - (string.length - (new_string.length - 5)));
                new_string = new_string + string.substring(start, string.length);
            }
        } else {
            new_string = string;
        }
        // return new string
        return new_string;
    }

    this.js_date_from_iso_date = function (dateText) {
        dateText = dateText.replace(/\D/g, " ");
        var dObj = dateText.split(" ");
        return new Date(dObj[0], (dObj[1] - 1), dObj[2]);
    }

    this.ucfirst = function (str) {
        // Makes a string's first character uppercase  
        // 
        // version: 1109.2015
        // discuss at: http://phpjs.org/functions/ucfirst
        // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   bugfixed by: Onno Marsman
        // +   improved by: Brett Zamir (http://brett-zamir.me)
        // *     example 1: ucfirst('kevin van zonneveld');
        // *     returns 1: 'Kevin van zonneveld'
        str += '';
        var f = str.charAt(0).toUpperCase();
        return f + str.substr(1);
    }
}