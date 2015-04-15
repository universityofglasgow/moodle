<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gismo;

class GISMOutil {

    public static function days_between_dates($dt1, $dt2) {
        // check for iso format Y-m-d H:i:s
        if (!preg_match('/^(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})[\s](\d{1,2})[\:\.](\d{1,2})[\:\.](\d{1,2})$/i', $dt1, $el)) {
            return false;
        }
        if (!preg_match('/^(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})[\s](\d{1,2})[\:\.](\d{1,2})[\:\.](\d{1,2})$/i', $dt2, $el)) {
            return false;
        }
        // build first date
        preg_match('/^(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})[\s](\d{1,2})[\:\.](\d{1,2})[\:\.](\d{1,2})$/i', $dt1, $el1);
        $date1 = mktime($el1[4], $el1[5], $el1[6], $el1[2], $el1[3], $el1[1]);
        // build second date
        preg_match('/^(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})[\s](\d{1,2})[\:\.](\d{1,2})[\:\.](\d{1,2})$/i', $dt2, $el2);
        $date2 = mktime($el2[4], $el2[5], $el2[6], $el2[2], $el2[3], $el2[1]);
        // evaluate days
        return round(abs($date1 - $date2) / 86400);
    }

    public static function days_between_times($t1, $t2) {
        // ensure integer greater or equals to 0
        $t1 = abs(intval($t1));
        $t2 = abs(intval($t2));
        // return result
        return round(floatval(abs($t2 - $t1)) / 86400.0);
    }

    public static function get_default_options() {
        return (object) array("include_hidden_items" => 1,
                    "matrix_num_series_limit" => 11,
                    "chart_base_color" => 1,
                    "include_hidden_items" => 1,
                    "resize_delay" => 1500,
                    "chart_axis_label_max_len" => 20,
                    "chart_axis_label_max_offset" => 5);
    }

    public static function gismo_error($id, $mode) {
        switch ($mode) {
            case "json":
                echo json_encode(array("error" => '1', "message" => get_string($id, 'block_gismo')));
                break;
            case "moodle":
            default:
                print_error(get_string($id, 'block_gismo'), "block_gismo");
                break;
        }
    }

    public static function this_month_first_day_time($time) {
        return mktime(0, 0, 0, date("m", $time), 1, date("Y", $time));
    }

    public static function this_month_last_day_time($time) {
        return mktime(0, 0, 0, date("m", $time) + 1, 0, date("Y", $time));
    }

    public static function next_month_first_day_time($time) {
        return mktime(0, 0, 0, date("m", $time) + 1, 1, date("Y", $time));
    }

    //Sort elements in array (used in usort)
    public static function sort_function($a, $b) {
        return (strcmp(strtolower($a->name), strtolower($b->name))); //lowercase all strings compare
    }

}

?>
