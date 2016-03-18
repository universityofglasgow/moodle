<?php

/**
 * GISMO block
 *
 * @package     block_gismo
 * @copyright   eLab Christian Milani
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//Fix from Corbière Alain - http://sourceforge.net/p/gismo/wiki/Home/#cf25
header("Content-type: application/json; charset=UTF-8");

// mode (json)
$error_mode = "json";

// libraries & acl
require_once "common.php";


$q = optional_param('q', '', PARAM_TEXT);
$from = optional_param('from', '', PARAM_INT);
$to = optional_param('to', '', PARAM_INT);
$subtype = optional_param('subtype', '', PARAM_TEXT);
$id = optional_param('id', '', PARAM_INT);
$restype = optional_param('restype', '', PARAM_TEXT);

// check input data
if (!isset($q) OR ! isset($from) OR ! isset($to)) {
    block_gismo\GISMOutil::gismo_error('err_missing_parameters', $error_mode);
    exit;
} else {
    $query = addslashes($q);
    $course_id = intval($srv_data->course_id);
    $from = intval($from);
    $to = intval($to);
}

// SECURITY (prevent users hacks)
$query = explode("@", $query);
$query = $actor . "@" . $query[1];

// current user id
$current_user_id = intval($USER->id);

// GET CONTEXT DATA (course, students)
// get course
$course = $DB->get_record("course", array("id" => $course_id));
if ($course === FALSE) {
    block_gismo\GISMOutil::gismo_error('err_course_not_set', $error_mode);
    exit;
}

// get users
$context = context_course::instance($course->id);
if ($context === FALSE) {
    block_gismo\GISMOutil::gismo_error('err_missing_course_students', $error_mode);
    exit;
}
$users = get_users_by_capability($context, "block/gismo:trackuser");
if ($users === FALSE) {
    block_gismo\GISMOutil::gismo_error('err_missing_course_students', $error_mode);
    exit;
}


// SQL FILTERS OFTEN/ALWAYS USED
// elaborate course filter
$course_sql = "course = ?";
$course_params = array($course_id);

// elaborate time filter
$time_sql = "time BETWEEN ? AND ?";
$time_params = array($from, $to);

// elaborate userid filter
$userid_sql = "";
$userid_params = array();
if (is_array($users) AND count($users) > 0) {
    list($userid_sql, $userid_params) = $DB->get_in_or_equal(array_keys($users));
    $userid_sql = "userid " . $userid_sql;
}

// course, time and users filters combined
$ctu_filters = implode(" AND ", array_filter(array($course_sql, $time_sql, $userid_sql)));  // remove null values / empty strings / ... before imploding
$ctu_params = array_merge($course_params, $time_params, $userid_params);


// BUILD RESULT
// result
$result = new stdClass();
$result->name = '';
$result->links = null;
$result->data = array();
$result->error = "";
$result->arr = array();
$result->context = $context;
$result->users = $users;

// extract data
switch ($query) {
    case "teacher@student-accesses":
    case "teacher@student-accesses-overview":
        // chart title
        switch ($query) {
            //LkM79 - error: it was the followinbg: case "student-accesses-overview":
            case "teacher@student-accesses-overview":
                $lang_index = "student_accesses_overview_chart_title";
                break;
            //LkM79 - error: it was the followinbg: case "student-accesses":
            case "teacher@student-accesses":
            default:
                $lang_index = "student_accesses_chart_title";
                break;
        }
        $result->name = get_string($lang_index, "block_gismo");
        // links
        $result->links = null;
        
        $student_resource_access = false;
        $ctu_filters .= " GROUP BY course, timedate, userid"; //BUG FIX WHEN GISMO EXPORTER RUN MORE THEN ONCE A DAY, we need to group by course,timedate & USERID
        $sort = "timedate ASC";
        $fields = " course, userid, timedate, sum(numval) as numval"; //BUG FIX WHEN GISMO EXPORTER RUN MORE THEN ONCE A DAY
        //postgreSQL solve problem on GROUP BY
        if ($CFG->dbtype === "pgsql") {
            $student_resource_access = $DB->get_records_sql("SELECT ROW_NUMBER() over(), a.* FROM (SELECT $fields"
                    . " FROM {block_gismo_sl}"
                    . " WHERE $ctu_filters"
                    . " ORDER BY $sort) as a", $ctu_params);
        } else {
            // chart data
            $student_resource_access = $DB->get_records_select("block_gismo_sl", $ctu_filters, $ctu_params, $sort, "id, " . $fields);
        }
        // build result
        if ($student_resource_access !== false) {
            // evaluate start date and end date
            // 1. get min date and max date
            // 2. from min date to first of the month
            //    from max date to last of the month
            // 3. evaluate difference in days between the two dates
            if (is_array($student_resource_access) AND count($student_resource_access) > 0) {
                // 1. min and max date
                $keys = array_keys($student_resource_access);
                $min_date = $student_resource_access[$keys[0]]->timedate;
                $max_date = $student_resource_access[$keys[count($student_resource_access) - 1]]->timedate;
                // adjust values
                $mid = explode("-", $min_date);
                $mad = explode("-", $max_date);
                $min_date = date("Y-m-d", mktime(0, 0, 0, $mid[1], 1, $mid[0]));
                $min_datetime = date("Y-m-d H:i:s", mktime(0, 0, 0, $mid[1], 1, $mid[0]));
                $max_date = date("Y-m-d", mktime(0, 0, 0, $mad[1] + 1, 0, $mad[0]));
                $max_datetime = date("Y-m-d H:i:s", mktime(0, 0, 0, $mad[1] + 1, 0, $mad[0]));
                // diff
                $days = intval(block_gismo\GISMOutil::days_between_dates($max_datetime, $min_datetime));
                // save results
                $extra_info = new stdClass();
                $extra_info->min_date = $min_date;
                $extra_info->max_date = $max_date;
                $extra_info->num_days = $days;
                $result->extra_info = $extra_info;
            }
            $result->data = $student_resource_access;
        }
        break;
    case "teacher@student-resources-access":
        switch ($subtype) {
            case "users-details":
                // check student id
                if (isset($id)) {
                    // chart title
                    $result->name = get_string("student_resources_details_chart_title", "block_gismo");
                    //$result->name = get_string("student_resources_overview_chart_title", "block_gismo");
                    // links
                    $result->links = "<a href='javascript:void(0);' onclick='javascript:g.analyse(\"student-resources-access\");'><img src=\"images/back.png\" alt=\"Close details\" title=\"Close details\" /></a>";
                    // filters
                    $filters = implode(" AND ", array_filter(array($course_sql, $time_sql, "userid = ?"))); // remove null values / empty strings / ... before imploding
                    $filters .= " GROUP BY  course, userid, restype, resid, timedate"; //BUG FIX WHEN GISMO EXPORTER RUN MORE THEN ONCE A DAY, we need to group by course,timedate & RESOURCEID!!!
                    $params = array_merge($course_params, $time_params, array(intval($id)));
                    $sort = "timedate ASC";
                    $fields = "course, userid, restype, resid, timedate, sum(numval) as numval"; //BUG FIX WHEN GISMO EXPORTER RUN MORE THEN ONCE A DAY
                    // get data
                    if ($CFG->dbtype === "pgsql") {
                        $student_resource_access = $DB->get_records_sql("SELECT ROW_NUMBER() over(), a.* FROM (SELECT $fields"
                                . " FROM {block_gismo_resource}"
                                . " WHERE $filters"
                                . " ORDER BY $sort) as a", $params);
                    } else {
                        $student_resource_access = $DB->get_records_select("block_gismo_resource", $filters, $params, $sort, "id, " . $fields); //BUG FIX WHEN GISMO EXPORTER RUN MORE THEN ONCE A DAY
                    }
                    // build result
                    if ($student_resource_access !== false) {
                        // evaluate start date and end date
                        // 1. get min date and max date
                        // 2. from min date to first of the month
                        //    from max date to last of the month
                        // 3. evaluate difference in days between the two dates
                        if (is_array($student_resource_access) AND count($student_resource_access) > 0) {
                            // 1. min and max date
                            $keys = array_keys($student_resource_access);
                            $min_date = $student_resource_access[$keys[0]]->timedate;
                            $max_date = $student_resource_access[$keys[count($student_resource_access) - 1]]->timedate;
                            // adjust values
                            $mid = explode("-", $min_date);
                            $mad = explode("-", $max_date);
                            $min_date = date("Y-m-d", mktime(0, 0, 0, $mid[1], 1, $mid[0]));
                            $min_datetime = date("Y-m-d H:i:s", mktime(0, 0, 0, $mid[1], 1, $mid[0]));
                            $max_date = date("Y-m-d", mktime(0, 0, 0, $mad[1] + 1, 0, $mad[0]));
                            $max_datetime = date("Y-m-d H:i:s", mktime(0, 0, 0, $mad[1] + 1, 0, $mad[0]));
                            // diff
                            $days = intval(block_gismo\GISMOutil::days_between_dates($max_datetime, $min_datetime));
                            // save results
                            $extra_info = new stdClass();
                            $extra_info->min_date = $min_date;
                            $extra_info->max_date = $max_date;
                            $extra_info->num_days = $days;
                            $result->extra_info = $extra_info;
                        }
                        $result->data = $student_resource_access;
                    }
                }
                break;
            default:
                // chart title
                $result->name = get_string("student_resources_overview_chart_title", "block_gismo");
                // get data
                $student_resource_access = $DB->get_records_select("block_gismo_resource", $ctu_filters, $ctu_params, "time ASC");
                // build result
                if ($student_resource_access !== false) {
                    $result->data = $student_resource_access;
                }
                break;
        }
        break;
    case "student@resources-students-overview":
        // overwrite userid filter (for max value)
        $userid_sql = "userid = ?";
        $userid_params = array($current_user_id);
        // overwrite ctu filters
        $ctu_filters = implode(" AND ", array_filter(array($course_sql, $time_sql, $userid_sql)));  // remove null values / empty strings / ... before imploding
        $ctu_params = array_merge($course_params, $time_params, $userid_params);
    case "teacher@resources-students-overview":
        // chart title
        $result->name = get_string("resources_students_overview_chart_title", "block_gismo");
        // links
        $result->links = null;
        // chart data
        $resource_accesses = $DB->get_records_select("block_gismo_resource", $ctu_filters, $ctu_params, "time ASC");
        // extra info (get max value)
        $ei_sql = implode(" AND ", array_filter(array($course_sql, $userid_sql))) . " GROUP BY userid, resid";
        $ei_params = array_merge($course_params, $userid_params);
        $ei = $DB->get_records_select("block_gismo_resource", $ei_sql, $ei_params, "value DESC", "MAX(id), SUM(numval) AS value", 0, 1);
        // save extra info
        $extra_info = (object) array("max_value" => 0);
        if (is_array($ei) AND count($ei) > 0) {
            $extra_info->max_value = array_pop($ei)->value;
        }
        // result
        if ($resource_accesses !== false) {
            $result->extra_info = $extra_info;
            $result->data = $resource_accesses;
        }
        break;
    case "teacher@resources-access":
        switch ($subtype) {
            case "resources-details":
                // check resource id
                if (isset($id)) {
                    // chart title
                    $result->name = get_string("student_resources_details_chart_title", "block_gismo");
                    // links
                    $result->links = "<a href='javascript:void(0);' onclick='javascript:g.analyse(\"resources-access\");'><img src=\"images/back.png\" alt=\"Close details\" title=\"Close details\" /></a>";
                    // filters
                    $filters = implode(" AND ", array_filter(array($course_sql, $time_sql, "resid = ?", "restype = ?")));  // remove null values / empty strings / ... before imploding
                    $filters .= " GROUP BY course, userid, restype, resid, timedate"; //BUG FIX WHEN GISMO EXPORTER RUN MORE THEN ONCE A DAY, we need to group by course,timedate, resrouceid & userid
                    $params = array_merge($course_params, $time_params, array(intval($id), $restype));
                    $sort = "timedate ASC";
                    $fields = "course, userid, restype, resid, timedate, sum(numval) as numval"; //BUG FIX WHEN GISMO EXPORTER RUN MORE THEN ONCE A DAY
                    // chart data
                    if ($CFG->dbtype === "pgsql") {
                        $resource_accesses = $DB->get_records_sql("SELECT ROW_NUMBER() over(), a.* FROM (SELECT $fields"
                                . " FROM {block_gismo_resource}"
                                . " WHERE $filters"
                                . " ORDER BY $sort) as a", $params);
                    } else {
                        $resource_accesses = $DB->get_records_select("block_gismo_resource", $filters, $params, $sort, "id, " . $fields);
                    }
                    // result
                    if ($resource_accesses !== false) {
                        // evaluate start date and end date
                        // 1. get min date and max date
                        // 2. from min date to first of the month
                        //    from max date to last of the month
                        // 3. evaluate difference in days between the two dates
                        if (is_array($resource_accesses) AND count($resource_accesses) > 0) {
                            // 1. min and max date
                            $keys = array_keys($resource_accesses);
                            $min_date = $resource_accesses[$keys[0]]->timedate;
                            $max_date = $resource_accesses[$keys[count($resource_accesses) - 1]]->timedate;
                            // adjust values
                            $mid = explode("-", $min_date);
                            $mad = explode("-", $max_date);
                            $min_date = date("Y-m-d", mktime(0, 0, 0, $mid[1], 1, $mid[0]));
                            $min_datetime = date("Y-m-d H:i:s", mktime(0, 0, 0, $mid[1], 1, $mid[0]));
                            $max_date = date("Y-m-d", mktime(0, 0, 0, $mad[1] + 1, 0, $mad[0]));
                            $max_datetime = date("Y-m-d H:i:s", mktime(0, 0, 0, $mad[1] + 1, 0, $mad[0]));
                            // diff
                            $days = intval(block_gismo\GISMOutil::days_between_dates($max_datetime, $min_datetime));
                            // save results
                            $extra_info = new stdClass();
                            $extra_info->min_date = $min_date;
                            $extra_info->max_date = $max_date;
                            $extra_info->num_days = $days;
                            $result->extra_info = $extra_info;
                        }
                        $result->data = $resource_accesses;
                    }
                }
                break;
            default:
                // chart title
                $result->name = get_string("resources_access_overview_chart_title", "block_gismo");
                // links
                $result->links = null;
                // chart data
                $resource_accesses = $DB->get_records_select("block_gismo_resource", $ctu_filters, $ctu_params, "time ASC");
                // result
                if ($resource_accesses !== false) {
                    $result->data = $resource_accesses;
                }
                break;
        }
        break;
    case "student@resources-access":
        // chart title
        $result->name = get_string("resources_access_overview_chart_title", "block_gismo");
        // links
        $result->links = null;
        // add filters to extract only data related to the student
        $ctu_filters .= "AND userid = ?";
        array_push($ctu_params, $current_user_id);
        // chart data
        $resource_accesses = $DB->get_records_select("block_gismo_resource", $ctu_filters, $ctu_params, "time ASC");
        // result
        if ($resource_accesses !== false) {
            $result->data = $resource_accesses;
        }
        break;
    case "teacher@assignments":
    case "student@assignments":
        // chart title
        $result->name = get_string("assignments_chart_title", "block_gismo");
        // links
        $result->links = null;
        // chart data
        $qry = "
                SELECT g.id, g.userid, g.grade, g.timemodified, a.id AS test_id, a.grade AS test_max_grade
                FROM {assign} AS a INNER JOIN {assign_grades} AS g ON a.id = g.assignment
                WHERE a.course = " . intval($course_id) . " AND g.timemodified BETWEEN " . $from . " AND " . $to . "
            ";
        // need to filter on user id ?
        if ($query === "student@assignments") {
            $qry .= " AND g.userid = " . $current_user_id;
        }
        $entries = $DB->get_records_sql($qry);
        // build result
        if (is_array($entries) AND count($entries) > 0 AND
                is_array($users) AND count($users) > 0) {
            foreach ($entries as $entry) {
                if (array_key_exists($entry->userid, $users)) {
                    $item = array(
                        "test_id" => $entry->test_id,
                        "test_max_grade" => $entry->test_max_grade,
                        "userid" => $entry->userid,
                        "user_grade" => $entry->grade,
                        "user_grade_label" => sprintf("%s / %s", format_float($entry->grade, 2), format_float($entry->test_max_grade, 2)),
                        "submission_time" => $entry->timemodified
                    );
                    // net to extract custom grade scale ?
                    if (intval($entry->test_max_grade) < 0 AND intval($entry->grade) !== -1) {
                        // scale id
                        $scale_id = abs($entry->test_max_grade);
                        // get scale
                        try {
                            $scale = $DB->get_field("scale", "scale", array("id" => $scale_id), MUST_EXIST);
                            $scale_values = explode(",", $scale);
                            $ug_idx = intval($entry->grade) - 1;
                            if (is_array($scale_values) AND count($scale_values) > 0 AND array_key_exists($ug_idx, $scale_values)) {
                                $item["test_max_grade"] = count($scale_values);
                                $item["user_grade_label"] = trim($scale_values[$ug_idx]);
                            }
                        } catch (Exception $e) {
                            echo "ERROR";
                        }
                    }
                    array_push($result->data, $item);
                }
            }
        }
        break;
    case "teacher@assignments22":
    case "student@assignments22":
        // chart title
        $result->name = get_string("assignments22_chart_title", "block_gismo");
        // links
        $result->links = null;
        // chart data (select s.id because the stupid moodle get_records__sql function set array key with the first selected field (use a unique key to avoid data loss))
        $qry = "
                SELECT s.id, s.userid, s.grade, s.timemarked, a.id AS test_id, a.grade AS test_max_grade
                FROM {assignment} AS a INNER JOIN {assignment_submissions} AS s ON a.id = s.assignment
                WHERE a.course = " . intval($course_id) . " AND s.timemodified BETWEEN " . $from . " AND " . $to . "
            ";
        // need to filter on user id ?
        if ($query === "student@assignments22") {
            $qry .= " AND s.userid = " . $current_user_id;
        }
        $entries = $DB->get_records_sql($qry);
        // build result
        if (is_array($entries) AND count($entries) > 0 AND
                is_array($users) AND count($users) > 0) {
            foreach ($entries as $entry) {
                if (array_key_exists($entry->userid, $users)) {
                    // standard item
                    $item = array(
                        "test_id" => $entry->test_id,
                        "test_max_grade" => $entry->test_max_grade,
                        "userid" => $entry->userid,
                        "user_grade" => $entry->grade, // -1 if it hasn't been corrected
                        "user_grade_label" => sprintf("%s / %s", $entry->grade, $entry->test_max_grade),
                        "test_timemarked" => $entry->timemarked // 0 if it hasn't been corrected
                    );
                    // net to extract custom grade scale ?
                    if (intval($entry->test_max_grade) < 0 AND intval($entry->grade) !== -1) {
                        // scale id
                        $scale_id = abs($entry->test_max_grade);
                        // get scale
                        try {
                            $scale = $DB->get_field("scale", "scale", array("id" => $scale_id), MUST_EXIST);
                            $scale_values = explode(",", $scale);
                            $ug_idx = intval($entry->grade) - 1;
                            if (is_array($scale_values) AND count($scale_values) > 0 AND array_key_exists($ug_idx, $scale_values)) {
                                $item["test_max_grade"] = count($scale_values);
                                $item["user_grade_label"] = trim($scale_values[$ug_idx]);
                            }
                        } catch (Exception $e) {
                            echo "ERROR";
                        }
                    }
                    // store item
                    array_push($result->data, $item);
                }
            }
        }
        break;
    case "teacher@quizzes":
    case "student@quizzes":
        // chart title
        $result->name = get_string("quizzes_chart_title", "block_gismo");
        // links
        $result->links = null;
        // chart data
        $qry = "
                SELECT g.id, g.userid, g.grade, g.timemodified, q.id AS test_id, q.grade AS test_max_grade, q.decimalpoints AS decimalpoints
                FROM {quiz} AS q INNER JOIN {quiz_grades} AS g ON q.id = g.quiz
                WHERE q.course = " . intval($course_id) . " AND g.timemodified BETWEEN " . $from . " AND " . $to . "
            ";
        // need to filter on user id ?
        if ($query === "student@quizzes") {
            $qry .= " AND g.userid = " . $current_user_id;
        }
        $entries = $DB->get_records_sql($qry);
        // build result
        if (is_array($entries) AND count($entries) > 0 AND
                is_array($users) AND count($users) > 0) {
            foreach ($entries as $entry) {
                if (array_key_exists($entry->userid, $users)) {
                    $item = array(
                        "test_id" => $entry->test_id,
                        "test_max_grade" => $entry->test_max_grade,
                        "userid" => $entry->userid,
                        "user_grade" => $entry->grade,
                        "user_grade_label" => sprintf("%s / %s", format_float($entry->grade, $entry->decimalpoints), format_float($entry->test_max_grade, $entry->decimalpoints)),
                        "submission_time" => $entry->timemodified
                    );
                    array_push($result->data, $item);
                }
            }
        }
        break;
    case "teacher@chats":
    case "teacher@forums":
    case "teacher@wikis":
        // specific info
        $spec_info = array(
            "teacher@chats" => array("title" => "chats_chart_title", "subtitle" => "chats_ud_chart_title", "activity" => "chat", "back" => "chats"),
            "teacher@forums" => array("title" => "forums_chart_title", "subtitle" => "forums_ud_chart_title", "activity" => "forum", "back" => "forums"),
            "teacher@wikis" => array("title" => "wikis_chart_title", "subtitle" => "wikis_ud_chart_title", "activity" => "wiki", "back" => "wikis")
        );
        switch ($subtype) {
            case "users-details":
                // user id filter
                $ctu_filters .= "AND userid = ?";
                array_push($ctu_params, (isset($id)) ? intval($id) : -1);
                // chart title
                $result->name = get_string($spec_info[$query]["subtitle"], "block_gismo");
                // links
                $result->links = "<a href='javascript:void(0);' onclick='javascript:g.analyse(\"" . $spec_info[$query]["back"] . "\");'><img src=\"images/back.png\" alt=\"Close details\" title=\"Close details\" /></a>";
                break;
            default:
                // chart title
                $result->name = get_string($spec_info[$query]["title"], "block_gismo");
                // links
                $result->links = null;
                break;
        }
        // add filters to extract data related to the selected activity only
        $ctu_filters .= "AND activity = ?";
        array_push($ctu_params, $spec_info[$query]["activity"]);
        // chart data
        
        $activity_data = $DB->get_records_select("block_gismo_activity", $ctu_filters, $ctu_params, "time ASC");
        // result
        $result->error = $ctu_filters;
        $result->arr = $ctu_params;
        
        if (is_array($activity_data) AND count($activity_data) > 0) {
            $result->data = $activity_data;
        }
        break;
    case "student@chats-over-time":
    case "student@forums-over-time":
    case "student@wikis-over-time":
        // add filters to extract data related to the current student only and then do
        // the same things as for teacher
        $ctu_filters .= "AND userid = ? ";
        array_push($ctu_params, $current_user_id);
    case "teacher@chats-over-time":
    case "teacher@forums-over-time":
    case "teacher@wikis-over-time":
        // specific info
        $spec_info = array(
            "teacher@chats-over-time" => array("title" => "chats_over_time_chart_title", "activity" => "chat"),
            "teacher@forums-over-time" => array("title" => "forums_over_time_chart_title", "activity" => "forum"),
            "teacher@wikis-over-time" => array("title" => "wikis_over_time_chart_title", "activity" => "wiki"),
            "student@chats-over-time" => array("title" => "chats_over_time_chart_title", "activity" => "chat"),
            "student@forums-over-time" => array("title" => "forums_over_time_chart_title", "activity" => "forum"),
            "student@wikis-over-time" => array("title" => "wikis_over_time_chart_title", "activity" => "wiki")
        );
        // chart title
        $result->name = get_string($spec_info[$query]["title"], "block_gismo");
        // links
        $result->links = null;
        // add filters to extract data related to the selected activity only
        $ctu_filters .= "AND activity = ? AND (context = ? OR context = ? OR context = ?)"; //sent or created or updated
        array_push($ctu_params, $spec_info[$query]["activity"]);
        array_push($ctu_params, "sent");
        array_push($ctu_params, "created");
        array_push($ctu_params, "updated");
        // chart data
        $activity_data = $DB->get_records_select("block_gismo_activity", $ctu_filters, $ctu_params, "time ASC");
        // result
        if (is_array($activity_data) AND count($activity_data) > 0) {
            // keys
            $keys = array_keys($activity_data);
            // extra info
            $extra_info = new stdClass();
            $extra_info->min_date = block_gismo\GISMOutil::this_month_first_day_time($activity_data[$keys[0]]->time);
            $extra_info->max_date = block_gismo\GISMOutil::this_month_last_day_time($activity_data[$keys[count($keys) - 1]]->time);
            $extra_info->num_days = intval(block_gismo\GISMOutil::days_between_times($extra_info->max_date, $extra_info->min_date));
            $extra_info->min_date = date("Y-m-d", $extra_info->min_date);
            $extra_info->max_date = date("Y-m-d", $extra_info->max_date);
            $result->extra_info = $extra_info;
            // save data
            $result->data = $activity_data;
        }
        break;
    case "student@chats":
    case "student@forums":
    case "student@wikis":
        // specific info
        $spec_info = array(
            "student@chats" => array("title" => "chats_chart_title", "activity" => "chat"),
            "student@forums" => array("title" => "forums_chart_title", "activity" => "forum"),
            "student@wikis" => array("title" => "wikis_chart_title", "activity" => "wiki")
        );
        // chart title
        $result->name = get_string($spec_info[$query]["title"], "block_gismo");
        // links
        $result->links = null;
        // add filters to extract data related to the selected activity only
        $ctu_filters .= "AND activity = ? AND userid = ? ";
        array_push($ctu_params, $spec_info[$query]["activity"]);
        array_push($ctu_params, $current_user_id);
        // chart data
        $activity_data = $DB->get_records_select("block_gismo_activity", $ctu_filters, $ctu_params, "time ASC");
        // result
        if (is_array($activity_data) AND count($activity_data) > 0) {
            // save data
            $result->data = $activity_data;
        }
        break;
    case "teacher@completion-assignments":
    case "student@completion-assignments":
        $itemtype = 'assign';
        // chart title
        $result->name = get_string("completion_assignment_chart_title", "block_gismo");
    case "teacher@completion-assignments22":
    case "student@completion-assignments22":
        if (!isset($itemtype)) {
            $itemtype = 'assignment';
            // chart title
            $result->name = get_string("completion_assignment22_chart_title", "block_gismo");
        }
    case "teacher@completion-resources":
    case "student@completion-resources":
        if (!isset($itemtype)) {
            $itemtype = "folder' OR m.name='imscp' OR m.name='page' OR m.name='resource' OR m.name='url' OR m.name='book";
            // chart title
            $result->name = get_string("completion_resource_chart_title", "block_gismo");
        }
    case "teacher@completion-forums":
    case "student@completion-forums":
        if (!isset($itemtype)) {
            $itemtype = 'forum';
            // chart title
            $result->name = get_string("completion_forum_chart_title", "block_gismo");
        }
    case "teacher@completion-wikis":
    case "student@completion-wikis":
        if (!isset($itemtype)) {
            $itemtype = 'wiki';
            // chart title
            $result->name = get_string("completion_wiki_chart_title", "block_gismo");
        }
    case "teacher@completion-chats":
    case "student@completion-chats":
        if (!isset($itemtype)) {
            $itemtype = 'chat';
            // chart title
            $result->name = get_string("completion_chat_chart_title", "block_gismo");
        }
    case "teacher@completion-quizzes":
    case "student@completion-quizzes":
        if (!isset($itemtype)) {
            $itemtype = 'quiz';
            // chart title
            $result->name = get_string("completion_quiz_chart_title", "block_gismo");
        }
        
        // links
        $result->links = null;
        // chart data
        //Completed when:
        //COMPLETION_COMPLETE = 1
        //COMPLETION_COMPLETE_PASS = 2
        //COMPLETION_COMPLETE_FAIL = 3
        
        $qry = "
                SELECT cmc.id as cmc_id, cm.instance as item_id, cmc.completionstate as completionstate, cmc.timemodified as timemodified, cmc.userid as userid, m.name as type
            FROM {course_modules_completion} cmc
            INNER JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
            INNER JOIN {modules} m ON cm.module = m.id
            WHERE (cmc.completionstate = 1 OR cmc.completionstate = 2)
            AND (m.name = '" . $itemtype . "')
            AND cm.course = " . intval($course_id) . " AND cmc.timemodified BETWEEN " . $from . " AND " . $to;
        
        // need to filter on user id ?
        if ($query === "student@completion-assignments" || $query === "student@completion-assignments22" || $query === "student@completion-chats" || $query === "student@completion-forums" || $query === "student@completion-wikis" || $query === "student@completion-quizzes" || $query === "student@completion-resources") {
            $qry .= " AND cmc.userid = " . $current_user_id;
        }
        $entries = $DB->get_records_sql($qry);
        
        // build result
        if (is_array($entries) AND count($entries) > 0 AND
                is_array($users) AND count($users) > 0) {
            foreach ($entries as $entry) {
                if (array_key_exists($entry->userid, $users)) {
                    $item = array(
                        "item_id" => $entry->item_id,
                        "completionstate" => $entry->completionstate,
                        "userid" => $entry->userid,
                        "timemodified" => $entry->timemodified,
                        "type" => $entry->type
                    );
                    array_push($result->data, $item);
                }
            }
        }
        break;
    default:
        break;
}

// echo json encoded result
echo json_encode($result);
?>