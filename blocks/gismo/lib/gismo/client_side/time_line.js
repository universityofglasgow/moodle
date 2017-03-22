/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function time_line(g) {
    // gismo instance
    this.gismo = g;

    // init method
    this.init = function () {
        // reference to the class
        var tl = this;
        // times
        csd = this.gismo.course_start_time * 1000;
        ct = this.gismo.current_time * 1000;
        // initial dates
        $("#" + this.gismo.from_date_id).val(this.format_date(new Date(csd)));
        $("#" + this.gismo.to_date_id).val(this.format_date(new Date(ct)));
        // slider
        $("#" + this.gismo.date_slider_id).slider({
            range: true,
            min: csd,
            max: ct,
            /* step: 86400 * 1000, */
            values: [csd, ct],
            slide: function (event, ui) {
                // update labels
                $("#" + tl.gismo.from_date_id).val(tl.format_date(new Date(tl.get_from(false))));
                $("#" + tl.gismo.to_date_id).val(tl.format_date(new Date(tl.get_to(false))));
            },
            change: function (event, ui) {
                if (tl.gismo.current_analysis["name"] != undefined && tl.gismo.current_analysis["name"] != null) {
                    tl.gismo.analyse(tl.gismo.current_analysis["type"], tl.gismo.current_analysis.options);
                }
            }
        });
        // datepickers
        $("#" + this.gismo.from_date_id).datepicker({minDate: new Date(csd),
            maxDate: new Date(ct),
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            onSelect: function (dateText, inst) {
                var from_date_value = tl.gismo.util.js_date_from_iso_date(dateText).getTime();
                var to_date_value = tl.get_to(false);
                if (from_date_value <= to_date_value) {
                    $("#" + tl.gismo.date_slider_id).slider('values', 0, from_date_value);
                } else {
                    $("#" + tl.gismo.date_slider_id).slider('values', 0, to_date_value);
                    $(this).val(tl.format_date(new Date(to_date_value)));
                }
            }});
        $("#" + this.gismo.to_date_id).datepicker({minDate: new Date(csd),
            maxDate: new Date(ct),
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            onSelect: function (dateText, inst) {
                var from_date_value = tl.get_from(false);
                var to_date_value = tl.gismo.util.js_date_from_iso_date(dateText).getTime();
                if (to_date_value >= from_date_value) {
                    $("#" + tl.gismo.date_slider_id).slider('values', 1, to_date_value);
                } else {
                    $("#" + tl.gismo.date_slider_id).slider('values', 1, from_date_value);
                    $(this).val(tl.format_date(new Date(from_date_value)));
                }
            }});
    }

    this.get_from_to = function (value_index, cast_to_seconds) {
        var value = $("#" + this.gismo.date_slider_id).slider('values', value_index);
        if (cast_to_seconds) {
            value = Math.floor(value / 1000);
        }
        return value;
    }

    this.get_from = function (cast_to_seconds) {
        return this.get_from_to(0, cast_to_seconds);
    }

    this.get_to = function (cast_to_seconds) {
        return this.get_from_to(1, cast_to_seconds);
    }

    this.format_date = function (date) {
        var dd = date.getDate();
        dd = (dd < 10) ? "0" + dd : dd;
        var mm = date.getMonth() + 1;
        mm = (mm < 10) ? "0" + mm : mm;
        var yyyy = date.getFullYear();
        var hh = date.getHours();
        hh = (hh < 10) ? "0" + hh : hh;
        var ii = date.getMinutes();
        ii = (ii < 10) ? "0" + ii : ii;
        return yyyy + "-" + mm + "-" + dd /*+ " " + hh + ":" + ii*/;
    }
}