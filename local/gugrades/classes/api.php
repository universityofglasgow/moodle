<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main API entry point.
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/constants.php');

/**
 * Actual implementation of all the external functions
 */
class api {

    /**
     * Get activities
     * @param int $courseid
     * @param int $categoryid
     * @return object List of activities/subcategories in
     */
    public static function get_activities(int $courseid, int $categoryid) {
        $tree = \local_gugrades\grades::get_activitytree($courseid, $categoryid);

        return $tree;
    }

    /**
     * Get capture page
     * @param int $courseid
     * @param int $gradeitemid
     * @param string $firstname (first letter of)
     * @param string $lastname (last letter of)
     * @param int $groupid
     * @param bool $viewfullnames
     * @return array
     */
    public static function get_capture_page(int $courseid, int $gradeitemid,
        string $firstname, string $lastname, int $groupid, bool $viewfullnames) {

        global $USER;

        //xhprof_enable(XHPROF_FLAGS_NO_BUILTINS);

        // Sanity checks for selected grade item.
        if (!\local_gugrades\grades::is_grade_supported($gradeitemid)) {
            return [
                'users' => [],
                'columns' => [],
                'hidden' => false,
                'itemtype' => '',
                'itemname' => '',
                'gradesupported' => false,
                'aggregationsupported' => false,
                'gradesimported' => false,
                'gradehidden' => false,
                'gradelocked' => false,
                'showconversion' => false,
                'converted' => false,
                'released' => false,
                'showcsvimport' => false,
                'staffuserid' => $USER->id,
            ];
        }

        // Cleanup unused columns for grade item.
        //\local_gugrades\grades::cleanup_empty_columns($gradeitemid);

        // Is aggregation supported for this gradeitem?
        $aggregationsupported = \local_gugrades\grades::are_all_grades_supported($courseid, $gradeitemid);

        // Hidden or locked in gradebook?
        [$gradehidden, $gradelocked] = \local_gugrades\grades::is_grade_hidden_locked($gradeitemid);

        // Instantiate object for this activity type.
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid, $groupid);
        $activity->set_name_filter($firstname, $lastname);
        $activity->set_viewfullnames($viewfullnames);

        // Should the conversion button be shown.
        $showconversion = \local_gugrades\grades::showconversion($gradeitemid);

        // Get list of users.
        // Will be everybody for 'manual' grades or filtered list for modules.
        $users = $activity->get_users();
        $users = \local_gugrades\grades::add_grades_to_user_records($courseid, $gradeitemid, $users, $gradehidden);
        $users = \local_gugrades\users::add_pictures_and_profiles_to_user_records($courseid, $users);
        $users = \local_gugrades\users::add_gradehidden_to_user_records($users, $gradeitemid);
        $columns = \local_gugrades\grades::get_grade_capture_columns($courseid, $gradeitemid);
        $gradesimported = \local_gugrades\grades::is_grades_imported($courseid, $gradeitemid);
        $converted = \local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid);
        $released = \local_gugrades\grades::is_grades_released($courseid, $gradeitemid);
        $showcsvimport = \local_gugrades\users::showcsvimport($users);

        //file_put_contents('/profiles/'.time().'.application.xhprof', serialize(xhprof_disable()));

        return [
            'users' => $users,
            'columns' => $columns,
            //'hidden' => $activity->is_names_hidden() || self::is_gradeitem_hidden($gradeitemid),
            'hidden' => $activity->is_names_hidden(),
            'itemtype' => $activity->get_itemtype(),
            'itemname' => $activity->get_itemname(),
            'gradesupported' => true,
            'aggregationsupported' => $aggregationsupported,
            'gradesimported' => $gradesimported,
            'gradehidden' => $gradehidden ? true : false,
            'gradelocked' => $gradelocked ? true : false,
            'showconversion' => $showconversion && $gradesimported,
            'converted' => $converted,
            'released' => $released,
            'showcsvimport' => $showcsvimport,
            'staffuserid' => $USER->id,
        ];
    }

    /**
     * Get capture page data only for a single user
     * Use after changes to that user (only)
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param bool $viewfullnames
     * @return array
     */
    public static function get_capture_user(int $courseid, int $gradeitemid, int $userid, int $viewfullnames) {

        // Check and get the user object.
        // Don't care about groups (really).
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid, 0);
        $activity->set_viewfullnames($viewfullnames);
        $user = $activity->get_user($userid);

        // Hidden or locked in gradebook?
        [$gradehidden, $gradelocked] = \local_gugrades\grades::is_grade_hidden_locked($gradeitemid);

        // Add/update the grades.
        $user = \local_gugrades\grades::add_grades_for_user($courseid, $gradeitemid, $user, $gradehidden);

        // Add/update picture
        $user = \local_gugrades\users::add_picture_and_profile_to_user_record($courseid, $user);

        // Add/update gradehidden
        $user = \local_gugrades\users::add_gradehidden_to_user_record($user, $gradeitemid);

        return $user;
    }

    /**
     * Unpack a (string) CSV file
     * @param string $csv
     * @return array
     */
    public static function unpack_csv($csv) {

        // First split into lines.
        $lines = explode(PHP_EOL, $csv);

        $data = [];
        foreach ($lines as $line) {
            if (trim($line)) {
                $items = array_map('trim', explode(',', $line));
                $data[] = $items;
            }
        }

        return $data;
    }

    /**
     * Get CSV download
     * Contents of pro-forma CSV file
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @return string
     */
    public static function get_csv_download(int $courseid, int $gradeitemid, int $groupid) {

        // Get activity object.
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid, $groupid);

        // Get array of users.
        $users = $activity->get_users();

        // Build CSV file.
        $csv = '';
        $csv .= get_string('name', 'local_gugrades') . ',' . get_string('idnumber', 'local_gugrades') . ',' .
            get_string('grade', 'local_gugrades') . PHP_EOL;
        foreach ($users as $user) {

            // MGU-897: Don't include users with no idnumber (we can't re-import them, anyway).
            if ($user->idnumber) {
                $csv .= $user->displayname . ',' . $user->idnumber . ',' . PHP_EOL;
            }
        }

        return $csv;
    }

    /**
     * CSV upload
     * Upload the data or (optionaly) just do a check
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param bool $testrun
     * @param string $reason
     * @param string $other
     * @param string $csv
     * @return array [$testrunlines, $errorcount, $addcount]
     */
    public static function csv_upload(int $courseid, int $gradeitemid, int $groupid,
        bool $testrun, string $reason, string $other, string $csv) {

        // Can we aggregate?
        $aggregationsupported = \local_gugrades\grades::are_all_grades_supported($courseid, $gradeitemid);

        // Turn csv into an array - and ditch first line.
        $lines = self::unpack_csv($csv);
        array_shift($lines);

        // If existing "other" then reason will look like OTHER_xxx,
        // where xxx is the columnid
        if (str_starts_with($reason, 'OTHER_')) {
            $other = \local_gugrades\grades::unpack_other($courseid, $reason);
            $reason = 'OTHER';
        };

        // Get the possible users for this grade item. And re-key by idnumber.
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid, $groupid);
        $users = $activity->get_users();
        $idusers = [];
        foreach ($users as $user) {
            if (!empty($user->idnumber)) {
                $idusers[$user->idnumber] = $user;
            }
        }

        // Get the grade mapping class.
        $mapping = \local_gugrades\grades::mapping_factory($courseid, $gradeitemid);

        // Only for testrun, accumulate output.
        $testrunlines = [];

        // Count success and misery (not all errors are fatal).
        $addcount = 0;
        $errorcount = 0;

        // Specific errors
        $errors = [
            'csvtoofewitems' => 0,
            'csvidinvalid' => 0,
            'csvgradeinvalid' => 0,
        ];

        // Because it can take a while.
        set_time_limit(0);

        // Counts for progress calculation.
        $numberoflines = count($lines);
        $count = 0;
        \local_gugrades\progress::initialise($courseid, 0, 'csvimport');

        // Iterate over CSV lines, checking and (optionally) adding new grade.
        foreach ($lines as $line) {

            // Need prefilled to keep web service return check happy.
            $testrunline = [
                'name' => '',
                'idnumber' => '',
                'grade' => '',
                'gradevalue' => 0.0,
                'state' => 0,
                'error' => '',
            ];

            // Progress.
            $progress = 100 * $count / $numberoflines;
            \local_gugrades\progress::record($courseid, 0, 'csvimport', $progress);
            $count++;

            // We just need the idnumber, so must have at least two entries.
            if (count($line) < 2) {
                $testrunline['error'] = get_string('csvtoofewitems', 'local_gugrades');
                $errors['csvtoofewitems']++;
                $testrunline['state'] = -1;
                $testrunlines[] = $testrunline;
                $errorcount++;
                continue;
            }

            // Get the data.
            $username = $line[0];
            $testrunline['name'] = $username;
            $idnumber = $line[1];
            $testrunline['idnumber'] = $idnumber;
            $grade = isset($line[2]) ? $grade = $line[2] : '';
            $testrunline['grade'] = $grade;

            // Check we have a (valid) idnumber.
            if (!isset($idusers[$idnumber])) {
                $testrunline['error'] = get_string('csvidinvalid', 'local_gugrades');
                $errors['csvidinvalid']++;
                $testrunline['state'] = -1;
                $testrunlines[] = $testrunline;
                $errorcount++;
                continue;
            }
            $user = $idusers[$idnumber];

            // Check if valid grade.
            if ($grade != '') {
                list($gradevalid, $gradevalue) = $mapping->csv_value($grade);
                if (!$gradevalid) {
                    $testrunline['error'] = get_string('csvgradeinvalid', 'local_gugrades');
                    $errors['csvgradeinvalid']++;
                    $testrunlines[] = $testrunline;
                    $errorcount++;
                    continue;
                }
                $testrunline['gradevalue'] = $gradevalue;
                $testrunline['state'] = 1;
            } else {
                $testrunline['error'] = get_string('csvnograde', 'local_gugrades'); // Warning.
                $testrunlines[] = $testrunline;
                continue;
            }

            $testrunlines[] = $testrunline;

            // If we get to here and not a testrun, we can actually save the data.
            if (!$testrun) {
                \local_gugrades\grades::write_grade(
                    courseid:       $courseid,
                    gradeitemid:    $gradeitemid,
                    userid:         $user->id,
                    admingrade:     '',
                    rawgrade:       $gradevalue,
                    convertedgrade: $gradevalue,
                    displaygrade:   $grade,
                    weightedgrade:  0.0,
                    gradetype:      $reason,
                    other:          $other,
                    iscurrent:      true,
                    iserror:        false,
                    auditcomment:   'CSV import',
                    ispoints:       !$mapping->is_scale(),
                );
                $addcount++;

                // Re-aggregate this user
                // DON'T, we'll do it in one hit at the end.
                //if ($aggregationsupported) {
                //    \local_gugrades\aggregation::aggregate_user_helper($courseid, $mapping->get_gradecategoryid(), $user->id);
                //}
            }
        }

        // Convert errors so web service friendly
        $errorlist = [];
        foreach ($errors as $str => $count) {
            if ($count) {
                $errorlist[] = [
                    'error' => get_string($str, 'local_gugrades'),
                    'count' => $count,
                ];
            }
        }

        // Progress complete. Show aggregation as normal spinner
        \local_gugrades\progress::terminate($courseid, 0, 'csvimport');

        if ($aggregationsupported && !$testrun) {
            $gradecategoryid = \local_gugrades\grades::get_gradecategoryid_from_gradeitemid($gradeitemid);
            self::recalculate($courseid, $gradecategoryid);
        }

        return [$testrunlines, $errorcount, $addcount, $errorlist];
    }

    /**
     * Get grade item
     * @param int $itemid
     * @return array
     */
    public static function get_grade_item(int $itemid) {
        global $DB;

        // Is grade supported at all?
        $gradesupported = \local_gugrades\grades::is_grade_supported($itemid);

        // Get item.
        $item = \local_gugrades\grades::get_gradeitem($itemid);
        $courseid = $item->courseid;

        // Get the mapping class.
        if ($gradesupported) {
            $mapping = \local_gugrades\grades::mapping_factory($courseid, $itemid);
        }

        // If the type is a category, get that as well.
        if ($item->itemtype == 'category') {
            $category = $DB->get_record('grade_categories', ['id' => $item->iteminstance], '*', MUST_EXIST);
            $categoryid = $item->iteminstance;
            $itemname = $category->fullname;

            // Get 'enhanced' version from aggregation.
            $enhancedcat = \local_gugrades\aggregation::get_enhanced_grade_category($category->courseid, $category->id);
        } else {
            $itemname = $item->itemname;
            $categoryid = $item->categoryid;
            $enhancedcat = null;
        }

        // Get the scale name.
        $scalename = $gradesupported ? $mapping->name() : 'Unsupported';

        // Get module name.
        if ($item->itemtype == 'mod') {
            $modname = get_string('pluginname', 'mod_' . $item->itemmodule);
        } else if ($item->itemtype == 'manual') {
            $modname = get_string('manual', 'local_gugrades');
        } else {
            $modname = get_string($item->itemtype);
        }

        // Does calculated category match real one?
        $categoryerror = false;
        if ($item->itemtype == 'category') {
            if ($enhancedcat->grademax != $item->grademax) {
                $categoryerror = true;
            }
        }

        // Get module details if it is a module.
        if (($item->itemtype == 'mod') && ($cm = \local_gugrades\grades::get_cm_from_gradeitemid($itemid))) {
            $linkobj = new \moodle_url('/mod/' . $item->itemmodule . '/view.php', ['id' => $cm->id]);
            $link = $linkobj->out();
        } else {
            $link = '';
        }

        return [
            'id' => $item->id,
            'courseid' => $item->courseid,
            'categoryid' => $categoryid,
            'itemname' => $itemname,
            'itemtype' => get_string($item->itemtype, 'local_gugrades'),
            'itemmodule' => $modname,
            'iteminstance' => $item->iteminstance,
            'isscale' => $gradesupported ? $mapping->is_scale() : false,
            'scalename' => $scalename,
            'grademax' => $item->itemtype == 'category' ? $enhancedcat->grademax : $item->grademax,
            'weight' => round($item->aggregationcoef * 100, PHP_ROUND_HALF_DOWN),
            'categoryerror' => $categoryerror,
            'link' => $link,
        ];
    }

    /**
     * get_levelonecategories
     * @param int $courseid
     * @return array
     */
    public static function get_levelonecategories(int $courseid) {
        $results = [];
        $categories = \local_gugrades\grades::get_firstlevel($courseid);
        foreach ($categories as $category) {
            $results[] = [
                'id' => $category->id,
                'fullname' => $category->fullname,
            ];
        }

        return $results;
    }

    /**
     * Check/convert fillns
     * (empty is permitted to save having to change a million tests)
     * @param string $fillns
     * @return string
     */
    protected static function check_fillns(string $fillns) {
        if (empty($fillns)) {
            return '';
        } else if ($fillns == 'none') {
            return '';
        } else if ($fillns == 'fillns') {
            return 'NOSUBMISSION';
        } else if ($fillns == 'fillns0') {
            return 'NOSUBMISSION_0';
        } else {
            throw new \moodle_exception('Fillns can only be none, fillns or fillns0. We found - "' . $fillns . '"');
        }
    }

    /**
     * Import grade
     * Note: $noaggregation intended for recursive imports so that we don't "over aggregate"
     * @param int $courseid
     * @param int $gradeitemid
     * @param \local_gugrades\mapping\base $mapping
     * @param \local_gugrades\activities\base $activity
     * @param int $userid
     * @param bool $additional
     * @param string $fillns
     * @param bool $noaggregation
     * @return bool - was a grade imported
     */
    public static function import_grade(
        int $courseid,
        int $gradeitemid,
        \local_gugrades\mapping\base $mapping,
        \local_gugrades\activities\base $activity,
        int $userid,
        bool $additional,
        string $fillns,
        bool $noaggregation = false,
        ) {

        $fillns = self::check_fillns($fillns);

        // If additional selected then skip users who already have data.
        if ($additional && \local_gugrades\grades::user_has_grades($gradeitemid, $userid)) {
            return false;
        }

        // Can we aggregate?
        if (!$noaggregation) {
            $aggregationsupported = \local_gugrades\grades::are_all_grades_supported($courseid, $gradeitemid);
        }

        // Ask activity for grade.
        $rawgrade = $activity->get_first_grade($userid);

        // If fillns not selected, then write whatever we got
        // MGU-1293: including null (no grade).
        if (empty($fillns) || (!empty($fillns) && !is_null($rawgrade))) {

            // Can (sometimes) come back as string, for some reason.
            if ($rawgrade) {
                $rawgrade = floatval($rawgrade);
            }

            [$convertedgrade, $displaygrade] = $mapping->import($rawgrade);

            // TODO: Is rawgrade correct? For scheduleB this will be completely
            // unrelated. E.g. rawgrade 6 = converted grade = 14.
            \local_gugrades\grades::write_grade(
                courseid:       $courseid,
                gradeitemid:    $gradeitemid,
                userid:         $userid,
                admingrade:     '',
                //rawgrade:       $rawgrade,
                rawgrade:       $convertedgrade,
                convertedgrade: $convertedgrade,
                displaygrade:   $displaygrade,
                weightedgrade:  0,
                gradetype:      'FIRST',
                other:          '',
                iscurrent:      true,
                iserror:        false,
                auditcomment:   get_string('import', 'local_gugrades'),
                ispoints:       !$mapping->is_scale(),
            );

            // Re-aggregate this user
            if (!$noaggregation && $aggregationsupported) {
                \local_gugrades\aggregation::aggregate_user_helper($courseid, $mapping->get_gradecategoryid(), $userid);
            }

            return true;

        } else {

            // If there's no grade and fillns is enabled, write
            // an NS grade, instead.
            [$displaygrade, ] = \local_gugrades\admingrades::get_displaygrade_from_name($fillns);

            \local_gugrades\grades::write_grade(
                courseid:       $courseid,
                gradeitemid:    $gradeitemid,
                userid:         $userid,
                admingrade:     $fillns,
                rawgrade:       0,
                convertedgrade: 0,
                displaygrade:   $displaygrade,
                weightedgrade:  0,
                gradetype:      'FIRST',
                other:          '',
                iscurrent:      true,
                iserror:        false,
                auditcomment:   get_string('import', 'local_gugrades'),
                ispoints:       false,
            );

            // Re-aggregate this user
            if (!$noaggregation) {
                \local_gugrades\aggregation::aggregate_user_helper($courseid, $mapping->get_gradecategoryid(), $userid);
            }

            return true;
        }

        return false;
    }

    /**
     * Get user picture url
     * @param int $userid
     * @return \moodle_url
     */
    public static function get_user_picture_url(int $userid) {
        global $DB, $PAGE;

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $userpicture = new \user_picture($user);

        return $userpicture->get_url($PAGE);
    }

    /**
     * Get user grades
     * Get site-wide grades for dashboard / Glasgow life / testing / etc.
     * @param int $userid
     * @return array
     */
    public static function get_user_grades(int $userid) {
        global $DB;

        throw new \moodle_exception('This function is now deprecated');

        // Load *current* grades for this user.
        if (!$grades = $DB->get_records('local_gugrades_grade', ['userid' => $userid, 'iscurrent' => 1])) {
            return [];
        }

        // We "cache" course objects so we don't keep looking them up.
        $courses = [];

        // Iterate over grades adding additional information.
        $newgrades = [];
        foreach ($grades as $grade) {
            $courseid = $grade->courseid;

            // Find course or just skip if it doesn't exist (deleted?).
            if (array_key_exists($courseid, $courses)) {
                $course = $courses[$courseid];
            } else {
                if (!$course = $DB->get_record('course', ['id' => $courseid])) {
                    continue;
                }
                $courses[$courseid] = $course;
            }

            // Add course data.
            $grade->coursefullname = $course->fullname;
            $grade->courseshortname = $course->shortname;

            // Additional grade data.
            $gradetype = $DB->get_record('local_gugrades_gradetype', ['id' => $grade->reason], '*', MUST_EXIST);
            $grade->reasonname = $gradetype->fullname;

            // Item into.
            $grade->itemname = grades::get_item_name_from_itemid($grade->gradeitemid);

            $newgrades[] = $grade;
        }

        return $newgrades;
    }

    /**
     * Get grade history for given user / grade item
     * @param int $gradeitemid
     * @param int $userid
     * @return array
     */
    public static function get_history(int $gradeitemid, int $userid) {
        global $DB;

        // Order by ID rather than time. As a second is quite a long time.
        $sql = "SELECT gg.*, gc.other FROM {local_gugrades_grade} gg
            JOIN {local_gugrades_column} gc ON gc.id = gg.columnid
            WHERE gg.userid = :userid
            AND gg.gradeitemid = :gradeitemid
            ORDER BY gg.id DESC";
        if (!$grades = $DB->get_records_sql($sql, ['userid' => $userid, 'gradeitemid' => $gradeitemid])) {
            return [];
        }

        // Additional info.
        $newgrades = [];
        foreach ($grades as $grade) {
            $grade->description = gradetype::get_description($grade->gradetype);
            $grade->time = userdate($grade->audittimecreated);
            $grade->current = $grade->iscurrent ? get_string('yes') : get_string('no');
            if ($audituser = $DB->get_record('user', ['id' => $grade->auditby])) {
                $grade->auditbyname = fullname($audituser);
            } else {
                $grade->auditbyname = '-';
            }

            if ($grade->gradetype == 'OTHER') {
                $grade->description .= ' (' . $grade->other . ')';
            }

            $newgrades[] = $grade;
        }

        return $newgrades;
    }

    /**
     * Get audit history
     * @param int $courseid
     * @return array
     */
    public static function get_audit(int $courseid) {
        global $USER, $DB;

        $context = \context_course::instance($courseid, true);
        if (has_capability('local/gugrades:readotheraudit', $context)) {
            $audit = \local_gugrades\audit::read($courseid);
        } else {
            $audit = \local_gugrades\audit::read($courseid, $USER->id);
        }

        return $audit;
    }

    /**
     * Has anything been defined for gradeitemid?
     * Are the grades going to "match" for a recursive import?
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @return array
     */
    public static function is_grades_imported(int $courseid, int $gradeitemid, $groupid) {
        $imported = \local_gugrades\grades::is_grades_imported($courseid, $gradeitemid, $groupid);
        list($recursiveavailable, $recursivematch, $allgradesvalid) = \local_gugrades\grades::recursive_import_match($gradeitemid);
        $level = \local_gugrades\grades::get_gradeitem_level($gradeitemid);

        return [
            'imported' => $imported,
            'recursiveavailable' => $recursiveavailable,
            'recursivematch' => $recursivematch,
            'allgradesvalid' => $allgradesvalid,
            'level' => $level,
        ];
    }

    /**
     * Import grades recursively. A basic import for all peers and children
     * of the supplied gradeitemid.
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param bool $additional
     * @param string $fillns
     * @return array [itemcount, gradecount]
     */
    public static function import_grades_recursive(int $courseid, int $gradeitemid, int $groupid, bool $additional, string $fillns) {
        global $DB;

        // This could legitimately take forever
        set_time_limit(0);

        // Check!
        list($recursiveavailable, $recursivematch, $allgradesvalid) = \local_gugrades\grades::recursive_import_match($gradeitemid);
        if (!$recursiveavailable) {
            throw new \moodle_exception("import_grades_recursive called for <2nd level grade item. ID = " . $gradeitemid);
        }

        // Get parent grade category.
        $gradeitem = \local_gugrades\grades::get_gradeitem($gradeitemid);
        $categoryid = $gradeitem->categoryid;
        $gradecategory = $DB->get_record('grade_categories', ['id' => $categoryid], '*', MUST_EXIST);

        // Get a list of all the grade items under the above.
        $items = \local_gugrades\grades::get_gradeitems_recursive($gradecategory);
        $itemcount = count($items);
        $usercount = \local_gugrades\users::count_enrolled_users($courseid);
        $transactioncount = $itemcount * $usercount;
        $gradecount = 0;

        // Counts are to track progress.
        $iitems = 0;
        \local_gugrades\progress::initialise($courseid, 0, 'import');
        foreach ($items as $item) {
            $activity = \local_gugrades\users::activity_factory($item->id, $courseid, $groupid);
            $mapping = \local_gugrades\grades::mapping_factory($courseid, $item->id);

            // Get all the permitted users in this activity.
            $users = $activity->get_users();

            // Iterate over these users importing grade.
            $iusers = 0;
            foreach ($users as $user) {

                // Import but do not aggregate
                if (self::import_grade($courseid, $item->id, $mapping, $activity, $user->id, $additional, $fillns, true)) {
                    $gradecount++;
                }
                $iusers++;

                // Calculate progress.
                // Note: $transactioncount is based on ALL enrolled students, which may not always be correct
                // ...but it'll be close enough.
                // This took a lot of thinking about. I'll leave it as an exercise for those who follow me :)
                $progress = floor((100 * $iitems / $itemcount) + (100 * $iusers / $itemcount / $usercount));
                \local_gugrades\progress::record($courseid, 0, 'import', $progress);
            }

            $iitems++;
        }

        // Terminate progress and just show 'please wait' for aggregation bit.
        \local_gugrades\progress::terminate($courseid, 0, 'import');

        // Finally, do the aggregation (once)
        self::recalculate($courseid, $categoryid);

        return [$itemcount, $gradecount];
    }

    /**
     * Get all the strings for this plugin as array of objects
     * @return array
     */
    public static function get_all_strings() {
        $stringmanager = get_string_manager();
        $lang = current_language();
        $cstrings = $stringmanager->load_component_strings('local_gugrades', $lang);

        $strings = [];
        foreach ($cstrings as $tag => $stringvalue) {
            $strings[] = [
                'tag' => $tag,
                'stringvalue' => $stringvalue,
            ];
        }

        return $strings;
    }

    /**
     * Convert array to FormKit menu
     * @param array $inputarray
     * @param bool $reverse
     * @return array (of objects)
     */
    private static function formkit_menu(array $inputarray, bool $reverse = false) {
        $menu = array_map(function($key, $value) {
            $item = new \stdClass;
            $item->value = $key;
            $item->label = $value;
            return $item;
        }, array_keys($inputarray), array_values($inputarray));

        if ($reverse) {
            $menu = array_reverse($menu, true);
        }

        return $menu;
    }

    /**
     * Get capture cell form
     * Various 'stuff' to construct the editable grade cells
     * @param int $courseid
     * @param int $gradeitemid
     * @return array
     */
    public static function get_capture_cell_form(int $courseid, int $gradeitemid) {
        global $DB;

        $converted = \local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid);

        // Gradeitem.
        list($itemtype, $gradeitem) = \local_gugrades\grades::analyse_gradeitem($gradeitemid);
        if ($gradeitem == false) {
            throw new \moodle_exception('Unsupported grade item encountered in get_add_grade_form. Gradeitemid = ' . $gradeitemid);
        }
        $grademax = ($gradeitem->gradetype == GRADE_TYPE_VALUE) ? $gradeitem->grademax : 0;

        // Scale.
        if ($converted) {
            $scale = \local_gugrades\conversion::get_conversion_scale($courseid, $gradeitemid);
            $scalemenu = self::formkit_menu($scale, true);
        } else if ($itemtype == 'scale') {
            $scale = \local_gugrades\grades::get_scale($gradeitem->scaleid);
            $scalemenu = self::formkit_menu($scale, true);
        } else if ($itemtype == 'scale22') {
            $scale = \local_gugrades\grades::get_scale(0);
            $scalemenu = self::formkit_menu($scale, true);
        } else {
            $scalemenu = [];
        }

        // Administrative grades.
        $admingrades = \local_gugrades\admingrades::get_menu($gradeitemid);
        $adminmenu = self::formkit_menu($admingrades, true);

        // Is it a scale?
        if ($converted) {
            $usescale = true;
        } else {
            $usescale = ($itemtype == 'scale') || ($itemtype == 'scale22');
        }

        return [
            'usescale' => $usescale,
            'grademax' => $grademax,
            'scalemenu' => $scalemenu,
            'adminmenu' => $adminmenu,
        ];
    }

    /**
     * Get add grade form for an aggregated category
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param object $gradeitem
     * @return array
     */
    public static function get_category_add_grade_form(int $courseid, int $gradeitemid, int $userid, object $gradeitem) {
        global $DB;

        // Sanity check
        if ($gradeitem->itemtype != 'category') {
            throw new \moodle_exception('Not a category');
        }

        // Get the aggregated category
        $category = \local_gugrades\aggregation::get_enhanced_grade_category($courseid, $gradeitem->iteminstance);

        // Is this scale or points?
        $isscale = !($category->atype == \local_gugrades\GRADETYPE_POINTS);

        // Conditions for overriding categories.
        // See MGU-997.  If this is Level 1 (i.e. we are trying to override a level 2 category)
        // then the grade MUST be a scale (including converted). We cannot override points.
        $level = \local_gugrades\grades::get_gradecategory_level($category->categoryid);
        $available = !(($level == 2) && ($category->atype == \local_gugrades\GRADETYPE_POINTS));

        // Get various menu items.
        $gradetypes = \local_gugrades\gradetype::get_menu($gradeitemid, LOCAL_GUGRADES_FORMENU);
        $wsgradetypes = self::formkit_menu($gradetypes);
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // If atype=E then we have an error condition
        $error = $category->atype == \local_gugrades\GRADETYPE_ERROR;

        // Get scalemenu
        if ($category->atype == \local_gugrades\GRADETYPE_SCHEDULEA) {
            $scale = \local_gugrades\grades::get_scale(0, 'schedulea');

            // MGU-1293: No grade option is not needed.
            unset($scale[-1]);
            $scalemenu = self::formkit_menu($scale, true);
        } else if ($category->atype == \local_gugrades\GRADETYPE_SCHEDULEB) {
            $scale = \local_gugrades\grades::get_scale(0, 'scheduleb');
            unset($scale[-1]);
            $scalemenu = self::formkit_menu($scale, true);
        } else {
            $scalemenu = [];
        }

        // Admin grades menu
        // Different for level == 1
        if ($level == 1) {
            $admingrades = \local_gugrades\admingrades::get_menu_level_one();
        } else {
            $admingrades = \local_gugrades\admingrades::get_menu($gradeitemid);
        }
        $adminmenu = self::formkit_menu($admingrades, true);

        // Is this already overridden in grade table
        $overridden = $DB->record_exists('local_gugrades_grade',
            ['gradeitemid' => $gradeitemid, 'userid' => $userid, 'iscurrent' => 1, 'catoverride' => 1]);

        return [
            'gradetypes' => $wsgradetypes,
            'rawgradetypes' => $gradetypes,
            'itemname' => $category->name,
            'fullname' => fullname($user),
            'idnumber' => $user->idnumber,
            'usescale' => $isscale,
            'iscategory' => true,
            'overridden' => $overridden,
            'available' => $available,
            'error' => $error,
            'grademax' => $category->grademax,
            'scalemenu' => $scalemenu,
            'adminmenu' => $adminmenu,
        ];
    }

    /**
     * Get add grade form
     * Various 'stuff' to construct the form
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @return array
     */
    public static function get_add_grade_form(int $courseid, int $gradeitemid, int $userid) {
        global $DB;

        // Check gradeitem.
        list($itemtype, $gradeitem) = \local_gugrades\grades::analyse_gradeitem($gradeitemid);
        if ($gradeitem == false) {
            throw new \moodle_exception('Unsupported grade item encountered in get_add_grade_form. Gradeitemid = ' . $gradeitemid);
        }

        // Is this actually a category?
        if ($itemtype == 'category') {

            return self::get_category_add_grade_form($courseid, $gradeitemid, $userid, $gradeitem);
        }

        // Has it been converted?
        $converted = \local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid);

        // Get "mapping" class.
        $mapping = \local_gugrades\grades::mapping_factory($courseid, $gradeitemid);

        // Get gradetype.
        $gradetypes = \local_gugrades\gradetype::get_menu($gradeitemid, LOCAL_GUGRADES_FORMENU);

        // If converted then we can't change existing points columns.
        if ($converted) {
            foreach ($gradetypes as $gradetype => $description) {
                if ($column = $DB->get_record('local_gugrades_column',
                    ['gradeitemid' => $gradeitemid, 'gradetype' => $gradetype])) {
                    if ($column->points) {
                        unset($gradetypes[$gradetype]);
                    }
                }
            }
        }

        $wsgradetypes = self::formkit_menu($gradetypes);

        // Username.
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Administrative grades.
        $admingrades = \local_gugrades\admingrades::get_menu($gradeitemid);
        $adminmenu = self::formkit_menu($admingrades, true);

        // Gradeitem.
        list($itemtype, $gradeitem) = \local_gugrades\grades::analyse_gradeitem($gradeitemid);
        if ($gradeitem == false) {
            throw new \moodle_exception('Unsupported grade item encountered in get_add_grade_form. Gradeitemid = ' . $gradeitemid);
        }

        // Get the right scale.
        $grademax = ($gradeitem->gradetype == GRADE_TYPE_VALUE) ? $gradeitem->grademax : 0;
        if ($converted) {
            $scale = \local_gugrades\conversion::get_conversion_scale($courseid, $gradeitemid);
            $scalemenu = self::formkit_menu($scale, true);
        } else if ($mapping->is_scale()) {
            if ($mapping->is_exactgrade22()) {
                $scale = \local_gugrades\grades::get_scale(0);
            } else {
                $scale = \local_gugrades\grades::get_scale($gradeitem->scaleid);
            }
            $scalemenu = self::formkit_menu($scale, true);
        } else {
            $scalemenu = [];
        }

        return [
            'gradetypes' => $wsgradetypes,
            'rawgradetypes' => $gradetypes,
            'itemname' => $gradeitem->itemname,
            'fullname' => fullname($user),
            'idnumber' => $user->idnumber,
            'usescale' => $mapping->is_scale() || $converted,
            'iscategory' => false,
            'overridden' => false,
            'available' => true,
            'error' => false,
            'grademax' => $grademax,
            'scalemenu' => $scalemenu,
            'adminmenu' => $adminmenu,
        ];
    }

    /**
     * Get menu of gradetypes and admin grades in menu format
     * @param int $courseid
     * @param int $gradeitemid
     * @return array [$gradetypes, $admingrades]
     */
    public static function get_gradetypes(int $courseid, int $gradeitemid) {
        global $DB;

        $gradetypes = \local_gugrades\gradetype::get_menu($gradeitemid, LOCAL_GUGRADES_FORMENU);

        // If converted then we can't change existing points columns.
        $converted = \local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid);
        if ($converted) {
            foreach ($gradetypes as $gradetype => $description) {
                if ($column = $DB->get_record('local_gugrades_column',
                    ['gradeitemid' => $gradeitemid, 'gradetype' => $gradetype])) {
                    if ($column->points) {
                        unset($gradetypes[$gradetype]);
                    }
                }
            }
        }
        $wsgradetypes = self::formkit_menu($gradetypes);

        // Administrative grades.
        $admingrades = \local_gugrades\admingrades::get_menu($gradeitemid);
        $adminmenu = self::formkit_menu($admingrades, true);

        return [$wsgradetypes, $adminmenu];
    }

    /**
     * Write additional grade
     * MGU-1293: $scale set to -1 is a proxy for No Grade.
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param string $reason
     * @param string $other
     * @param string $admingrade
     * @param int $scale
     * @param float $grade
     * @param string $notes
     * @param bool $delete
     */
    public static function write_additional_grade(
        int $courseid,
        int $gradeitemid,
        int $userid,
        string $reason,
        string $other,
        string $admingrade,
        int $scale,
        float $grade,
        string $notes,
        bool $delete = false
        ) {

        global $DB;

        // Can we aggregate?
        $aggregationsupported = \local_gugrades\grades::are_all_grades_supported($courseid, $gradeitemid);

        // Conversion class.
        $mapping = \local_gugrades\grades::mapping_factory($courseid, $gradeitemid);

        // Get the stuff we used to build the form for validation.
        $form = self::get_add_grade_form($courseid, $gradeitemid, $userid);

        // If form says that add/convert is not available then it's an exception.
        if (!$form['available']) {
            throw new \moodle_exception('Cannot override grade at this level');
        }

        // Check 'reason' is valid.
        // Pseudo-reason of CATEGORY is permitted.
        $gradetypes = $form['rawgradetypes'];
        if (!array_key_exists($reason, $gradetypes) && ($reason != 'CATEGORY')) {
            throw new \moodle_exception('Attempting to write invalid reason - "' . $reason . '"');
        }

        // If reason looks like OTHER_XX then it's an extant other type. XX is the ID
        // in local_gugrades_column. So...
        if (str_contains($reason, 'OTHER_')) {
            $parts = explode('_', $reason);
            $reason = 'OTHER';
            $columnid = $parts[1];
            $column = $DB->get_record('local_gugrades_column', ['id' => $columnid], '*', MUST_EXIST);
            $other = $column->other;
        }

        // Check 'other' is valid.
        if ($other && ($reason != 'OTHER')) {
            throw new \moodle_exception('Attemting to write invalid other text when reason is not other');
        }
        if (!$other && ($reason == 'OTHER')) {
            throw new \moodle_exception('Attempting to write empty other text when reason is other');
        }

        // Check 'scale' is valid.
        $usescale = $form['usescale'];
        if (!$usescale && ($scale != 0)) {
            throw new \moodle_exception('Attempting to write scale value when item is not a scale');
        }

        // Check if 'grade' is valid.
        if ($usescale && ($grade != 0)) {
            throw new \moodle_exception('Attempting to write non-zero grade when item type is a scale');
        }

        // Get converted and display grade.
        if (!empty($admingrade)) {
            $rawgrade = 0;
            $convertedgrade = 0.0;
            [$code, ] = \local_gugrades\admingrades::get_displaygrade_from_name($admingrade);
            $displaygrade = $code;
        } else if ($usescale) {

            // MGU-1293: Check for -1, which is No grade
            if ($scale == -1) {
                $displaygrade = get_string('nograde', 'local_gugrades');
                $rawgrade = null;
                $convertedgrade = null;
            } else {
                $displaygrade = $mapping->get_band($scale);
                $rawgrade = $scale;
                $convertedgrade = $scale;
            }
        } else {
            $displaygrade = $grade;
            $rawgrade = $grade;
            $convertedgrade = $grade;
        }

        // If we're overriding a category then set the override bit
        $catoverride = $reason == 'CATEGORY';

        // gradeitem must be a category if reason = category and must not be if not
        $iscategory = \local_gugrades\grades::is_gradeitemid_category($gradeitemid);
        if (($catoverride && !$iscategory) || (!$catoverride && $iscategory)) {
            throw new \moodle_exception('Category reason / itemtype mismatch. gradeitemid = ' . $gradeitemid . ', reason = "' . $reason . '"');
        }

        // If cateoverride and delete is true then
        // we are removing the cat override and aggregating a new grade.
        if ($catoverride && $delete) {
            \local_gugrades\grades::remove_catoverride($gradeitemid, $userid);
        } else {

            // Happy as we're going to get, so write the new data.
            // overwrite is set to false to indicate that this is a 'new' grade
            \local_gugrades\grades::write_grade(
                courseid:       $courseid,
                gradeitemid:    $gradeitemid,
                userid:         $userid,
                admingrade:     $admingrade,
                rawgrade:       $rawgrade,
                convertedgrade: $convertedgrade,
                displaygrade:   $displaygrade,
                weightedgrade:  0,
                gradetype:      $reason,
                other:          $other,
                iscurrent:      true,
                iserror:        false,
                auditcomment:   $notes,
                ispoints:       !$mapping->is_scale(),
                overwrite:      false,
                catoverride:    $catoverride,
            );
        }

        // Re-aggregate this user
        if ($aggregationsupported) {
            \local_gugrades\aggregation::aggregate_user_helper($courseid, $mapping->get_gradecategoryid(), $userid);
        }

    }

    /**
     * Add an aditional column (if it doesn't exist already)
     * TODO: Something about notes.
     * @param int $courseid
     * @param int $gradeitemid
     * @param string $reason
     * @param string $other
     * @param string $notes
     */
    public static function write_column(int $courseid, int $gradeitemid, string $reason, string $other, string $notes) {

        // Need column points or scale.
        // If it's converted then it must be scale, otherwise it's whatever the conversion factory thinks.
        $converted = \local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid);
        if ($converted) {
            $points = false;
        } else {
            $mapping = \local_gugrades\grades::mapping_factory($courseid, $gradeitemid);
            $points = !$mapping->is_scale();
        }
        $column = \local_gugrades\grades::get_column($courseid, $gradeitemid, $reason, $other, $points);

        return $column->id;
    }

    /**
     * Save settings
     * Options in tool UI (not sitewide settings)
     * @param int $courseid
     * @param int $gradeitemid (0 if you don't need it)
     * @param array $settings
     */
    public static function save_settings(int $courseid, int $gradeitemid, array $settings) {
        global $DB;

        foreach ($settings as $setting) {
            $config = $DB->get_record('local_gugrades_config', [
                'courseid' => $courseid,
                'gradeitemid' => $gradeitemid,
                'name' => $setting['name'],
            ]);
            if ($config) {
                $config->value = $setting['value'];
                $DB->update_record('local_gugrades_config', $config);
            } else {
                $config = new \stdClass;
                $config->courseid = $courseid;
                $config->gradeitemid = $gradeitemid;
                $config->name = $setting['name'];
                $config->value = $setting['value'];
                $DB->insert_record('local_gugrades_config', $config);
            }
        }
    }

    /**
     * Get settings
     * @param int $courseid
     * @param int $gradeitemid (probably 0)
     * @return array
     */
    public static function get_settings(int $courseid, int $gradeitemid) {
        global $DB;

        $configs = $DB->get_records('local_gugrades_config', ['courseid' => $courseid, 'gradeitemid' => $gradeitemid]);
        $settings = [];
        foreach ($configs as $config) {
            $settings[] = [
                'name' => $config->name,
                'value' => $config->value,
            ];
        }

        return $settings;
    }

    /**
     * Is MyGrades "enabled" for this course
     * Are there any released grades and/or is MyGrades
     * disabled for this course in the settings
     * dashboardenabled == grades released AND !$disabledashboard
     * gradesreleased == grades released
     * @param int $courseid
     * @return [bool, bool]
     */
    public static function get_dashboard_enabled(int $courseid) {
        global $DB;

        // Get setting. If that's set to disabled then nothing else to do.
        $sqlname = $DB->sql_compare_text('name');
        $sql = "SELECT * FROM {local_gugrades_config}
            WHERE courseid = :courseid
            AND $sqlname = :name
            AND value = :value";
        if ($DB->record_exists_sql($sql, ['courseid' => $courseid, 'name' => 'disabledashboard', 'value' => 1])) {
            $disabledashboard = true;
        } else {
            $disabledashboard = false;
        }

        // If not disabled, then check if there are any 'released' grades.
        // If not, dashboard is disabled (again).
        if ($DB->record_exists('local_gugrades_grade', ['courseid' => $courseid, 'gradetype' => 'RELEASED', 'iscurrent' => 1])) {
            $gradesreleased = true;
        } else {
            $gradesreleased = false;
        }

        return [$gradesreleased && !$disabledashboard, $gradesreleased];
    }

    /**
     * Deprecated function name
     * @param int $courseid
     * @return boolean
     */
    public static function is_mygrades_enabled_for_course(int $courseid) {
        [$gradesenabled, $gradesreleased] = self::get_dashboard_enabled($courseid);

        return $gradesenabled;
    }

    /**
     * Check if MyGrades custom course field is enabled
     * @param int $courseid
     * @return bool
     */
    public static function is_mygrades_customfield_enabled(int $courseid) {
        global $DB;

        // Find the custom field
        $field = $DB->get_record('customfield_field', ['shortname' => 'studentmygrades'], '*', MUST_EXIST);

        // See if there is a data field for this
        if ($data = $DB->get_record('customfield_data', ['fieldid' => $field->id, 'instanceid' => $courseid])) {
            $enabled = $data->value;
        } else {
            $enabled = false;
        }

        return (Boolean)$enabled;
    }

    /**
     * Get list of user's courses
     * (and first level categories)
     * @param int $userid UserID of student
     * @param bool $current Return only current courses
     * @param bool $past Return only past courses
     * @param string $sort Comma separated list of fields to sort by
     * @return array
     */
    public static function dashboard_get_courses(int $userid, bool $current, bool $past, string $sort) {
        global $DB, $USER;

        // startdate filter value.
        $startdateafter = get_config('local_gugrades', 'startdateafter');

        // If this isn't current user, do they have the rights to look at other users.
        $context = \context_system::instance();

        // Get basic list of enrolments for this user.
        $additionalfields = [
            'enddate',
            'showgrades',
        ];

        // If in doubt, just sort on fullname.
        if (!$sort) {
            $sort = 'fullname';
        }
        $courses = enrol_get_users_courses($userid, true, $additionalfields, $sort);

        // Run through courses to establish which have gugrades/GCAT enabled
        // and also add TL grade category data.
        foreach ($courses as $id => $course) {
            $context = \context_course::instance($id, true);

            // Skip courses which don't have enabled in the customfield.
            if (!self::is_mygrades_customfield_enabled($id)) {
                unset($courses[$id]);
                continue;
            }

            // Current/past cutoff is enddate.
            $cutoffdate = $course->enddate;

            // If current selected only return 'current' courses
            // enddate == 0 is taken to be current, regardless.
            if ($current) {
                if ($course->enddate && ($cutoffdate <= time())) {
                    unset($courses[$id]);
                    continue;
                }
            }

            // If past is selected only return past courses
            // enddate == 0 is taken to be NOT past, regardless.
            // startdate has to be >= startdateafter.
            if ($past) {
                if (!$course->enddate || (time() < $cutoffdate)) {
                    unset($courses[$id]);
                    continue;
                }
                if ($course->startdate < $startdateafter) {
                    unset($courses[$id]);
                    continue;
                }
            }

            // These always need to exist for the webservice checks.
            $course->gugradesenabled = false;
            $course->gcatenabled = false;
            $course->firstlevel = [];

            // Check if MyGrades is enabled for this course?
            [$course->gugradesenabled, $gradesreleased] = self::get_dashboard_enabled($id);

            // Get first-level grade categories.
            $categories = \local_gugrades\grades::get_firstlevel($id);
            foreach ($categories as $category) {
                $course->firstlevel[] = [
                    'id' => $category->id,
                    'fullname' => $category->fullname,
                ];
            }
        }

        return $courses;
    }

    /**
     * Get grades and subcategories for given user and grade category
     * TODO: This will need a bit of filling out.
     * @param int $userid
     * @param int $gradecategoryid
     * @return array
     */
    public static function dashboard_get_grades(int $userid, int $gradecategoryid) {
        global $DB, $USER;

        // Get grade category and make some basic checks.
        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $courseid = $gradecategory->courseid;
        $context = \context_course::instance($courseid, true);

        // If this isn't current user, do they have the rights to look at other users.
        /*
        if ($USER->id != $userid) {
            require_capability('local/gugrades:readotherdashboard', $context);
        } else {
            require_capability('local/gugrades:readdashboard', $context);
        }
            */

        // TODO: Get grades.
        $grades = \local_gugrades\grades::get_dashboard_grades($userid, $gradecategoryid);

        // Get child categories.
        $childcategories = $DB->get_records('grade_categories', ['parent' => $gradecategoryid]);

        return [
            'grades' => $grades,
            'childcategories' => $childcategories,
        ];
    }

    /**
     * Release grade for single user.
     * In practice, this would/should only be used to re-release the updated
     * grade where the overall item has already been released.
     * If valid activity factory passed it will be used. If null it is looked up.
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param object $activity
     */
    public static function release_user_grade(int $courseid, int $gradeitemid, int $userid, object $activity = null) {
        global $DB;

        // Look up actvity object if we need to
        if (!$activity) {
            $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid, 0);
        }

        // Can we aggregate?
        $aggregationsupported = \local_gugrades\grades::are_all_grades_supported($courseid, $gradeitemid);

        // Is it an aggregated category
        if (!$released = \local_gugrades\grades::get_aggregated_from_gradeitemid($gradeitemid, $userid)) {

            // Nope. So get 'normal' grade.
            //$usercapture = new usercapture($courseid, $gradeitemid, $userid);
            $usercapture = \local_gugrades\usercapture::create($courseid, $gradeitemid, $userid);
            $released = $usercapture->get_released();
        }

        // Don't bother if grade is in error.
        if ($released && !$released->iserror) {
            \local_gugrades\grades::write_grade(
                courseid: $courseid,
                gradeitemid: $gradeitemid,
                userid: $userid,
                admingrade: $released->admingrade,
                rawgrade: $released->rawgrade,
                convertedgrade: $released->convertedgrade,
                displaygrade: $released->displaygrade,
                weightedgrade: $released->weightedgrade,
                gradetype: 'RELEASED',
                other: '',
                iscurrent: true,
                iserror: false,
                auditcomment: 'Release grades',
                ispoints: $released->points,
            );

            // Re-aggregate this user
            if ($aggregationsupported) {
                $mapping = \local_gugrades\grades::mapping_factory($courseid, $gradeitemid);
                \local_gugrades\aggregation::aggregate_user_helper($courseid, $mapping->get_gradecategoryid(), $userid);
            }
        }

        // Activity action .
        $activity->release_grades($userid);
    }

    /**
     * Release grades
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param bool $revert
     */
    public static function release_grades(int $courseid, int $gradeitemid, int $groupid, bool $revert) {
        global $DB;

        // Get list of users.
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid, $groupid);
        $users = $activity->get_users();

        // Iterate over users releasing/reverting grades.
        foreach ($users as $user) {
            if ($revert) {

                // If reverting, mark any grades marked RELEASED for this
                // gradeitemid / user as not current.
                $sql = 'UPDATE {local_gugrades_grade}
                    SET iscurrent = 0
                    WHERE gradetype = "RELEASED"
                    AND gradeitemid = :gradeitemid
                    AND userid = :userid';
                $DB->execute($sql, ['gradeitemid' => $gradeitemid, 'userid' => $user->id]);

                // Remove any columns that this has orphaned.
                \local_gugrades\grades::cleanup_empty_columns($gradeitemid);

                // Activity action.
                $activity->unrelease_grades($user->id);
            } else {
                self::release_user_grade($courseid, $gradeitemid, $user->id, $activity);
            }
        }
    }

    /**
     * Reset all caches
     */
    public static function reset_all_caches() {
        $caches = ['gradeitems', 'availableusers', 'useraggdata', 'progress', 'provisionalgrade'];
        foreach ($caches as $cache) {
            $cache = \cache::make('local_gugrades', $cache);
            $cache->purge();
        }
    }

    /**
     * Reset MyGrades for course
     * @param int $courseid
     */
    public static function reset(int $courseid) {
        global $DB, $GRADEITEMS;

        // Delete grades.
        $DB->delete_records('local_gugrades_grade', ['courseid' => $courseid]);

        // Delete columns.
        $DB->delete_records('local_gugrades_column', ['courseid' => $courseid]);

        // Delete maps.
        $maps = $DB->get_records('local_gugrades_map', ['courseid' => $courseid]);
        foreach ($maps as $map) {
            $DB->delete_records('local_gugrades_map_value', ['mapid' => $map->id]);
        }
        $DB->delete_records('local_gugrades_map_item', ['courseid' => $courseid]);
        $DB->delete_records('local_gugrades_map', ['courseid' => $courseid]);

        // Delete resit required.
        $DB->delete_records('local_gugrades_resitrequired', ['courseid' => $courseid]);

        // Delete hidden.
        $DB->delete_records('local_gugrades_hidden', ['courseid' => $courseid]);

        // Delete altered weight.
        $DB->delete_records('local_gugrades_altered_weight', ['courseid' => $courseid]);

        // "Cached" gradeitems.
        $GRADEITEMS = [];

        // As this will be a rarely used function we will take the liberty of purging the caches.
        self::reset_all_caches();
    }

    /**
     * Get course groups
     * @param int $courseid
     * @return array
     *
     */
    public static function get_groups(int $courseid) {
        global $DB;

        $groups = $DB->get_records('groups', ['courseid' => $courseid]);

        return $groups;
    }

    /**
     * Get conversion maps
     * @param int $courseid
     * @return array
     */
    public static function get_conversion_maps(int $courseid): array {
        $maps = \local_gugrades\conversion::get_maps($courseid);
        foreach ($maps as $map) {
            $map->inuse = \local_gugrades\conversion::inuse($map->id);
        }

        return $maps;
    }

    /**
     * Get conversion map, given mapid
     * If mapid = 0 then return default mapping values
     * @param int $courseid
     * @param int $mapid
     * @param string $schedule
     * @return int
     */
    public static function get_conversion_map(int $courseid, int $mapid, string $schedule): array {

        // If mapid = 0, then get the new/default map.
        if ($mapid == 0) {
            $map = \local_gugrades\conversion::get_default_map($schedule);

            return [
                'name' => '',
                'schedule' => $schedule,
                'maxgrade' => 100,
                'inuse' => false,
                'map' => $map,
            ];
        } else {
            return \local_gugrades\conversion::get_map_for_editing($mapid);
        }
    }

    /**
     * Write conversion map, mapid=0 means a new one
     * @param int $courseid
     * @param int $mapid
     * @param string $name
     * @param string $schedule
     * @param float $maxgrade
     * @param array $map
     * @return int
     */
    public static function write_conversion_map(
        int $courseid, int $mapid, string $name, string $schedule, float $maxgrade, array $map): int {
        $mapid = \local_gugrades\conversion::write_conversion_map($courseid, $mapid, $name, $schedule, $maxgrade, $map);

        return $mapid;
    }

    /**
     * Delete conversion map
     * @param int $courseid
     * @param int $mapid
     * @return bool
     */
    public static function delete_conversion_map(int $courseid, int $mapid) {
        return \local_gugrades\conversion::delete_conversion_map($courseid, $mapid);
    }

    /**
     * Import conversion map (as a new one)
     * @param int $courseid
     * @param string $jsonmap
     * @return int
     */
    public static function import_conversion_map(int $courseid, string $jsonmap) {
        return \local_gugrades\conversion::import_conversion_map($courseid, $jsonmap);
    }

    /**
     * Select conversion (map).
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $gradecategoryid
     * @param int $mapid
     */
    public static function select_conversion(int $courseid, int $gradeitemid, int $gradecategoryid, int $mapid) {
        \local_gugrades\conversion::select_conversion($courseid, $gradeitemid, $gradecategoryid, $mapid);
    }

    /**
     * get select conversion (map) info.
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $gradecategoryid
     * @return array
     */
    public static function get_selected_conversion(int $courseid, int $gradeitemid, int $gradecategoryid) {
        return \local_gugrades\conversion::get_selected_conversion($courseid, $gradeitemid, $gradecategoryid);
    }

    /**
     * Show/hide grade
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param bool $hide
     */
    public static function show_hide_grade(int $courseid, int $gradeitemid, int $userid, bool $hide) {
        global $DB;

        if (!$hide) {
            $DB->delete_records('local_gugrades_hidden', ['gradeitemid' => $gradeitemid, 'userid' => $userid]);
        } else {
            if (!$DB->record_exists('local_gugrades_hidden', ['gradeitemid' => $gradeitemid, 'userid' => $userid])) {
                $hidden = new \stdClass();
                $hidden->courseid = $courseid;
                $hidden->gradeitemid = $gradeitemid;
                $hidden->userid = $userid;
                $DB->insert_record('local_gugrades_hidden', $hidden);
            }
        }
    }

    /**
     * Is grade item hidden in Gradebook
     * @param int $gradeitemid
     * @return bool
     */
    protected static function is_gradeitem_hidden(int $gradeitemid) {
        global $DB;

        return $DB->record_exists('grade_items', ['id' => $gradeitemid, 'hidden' => 1]);
    }

    /**
     * Check if grade is hidden
     * @param int $gradeitemid
     * @param int $userid
     * @return boolean
     */
    public static function is_grade_hidden(int $gradeitemid, int $userid) {
        global $DB;

        // Check (internal) MyGrades hidden flag.
        $mygradeshidden = $DB->record_exists('local_gugrades_hidden', ['gradeitemid' => $gradeitemid, 'userid' => $userid]);

        // Check grade item hidden in Gradebook.
        $gradeitemhidden = self::is_gradeitem_hidden($gradeitemid);

        // Check user grade hidden.
        $gradegradehidden = $DB->record_exists('grade_grades', ['itemid' => $gradeitemid, 'userid' => $userid, 'hidden' => 1]);

        // Any being hidden counts.
        return $mygradeshidden || $gradeitemhidden || $gradegradehidden;
    }

    /**
     * Get explain aggregation
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $userid
     * @return array
     */
    public static function get_explain_aggregation(int $courseid, int $gradecategoryid, int $userid) {

        // Aggregate and get 'explanation'.
        $aggregated_result = \local_gugrades\aggregation::aggregate_user_helper($courseid, $gradecategoryid, $userid);
        [,,,,,,$explain] = $aggregated_result;

        // Get categories and items at this level.
        [$columns, $atype, $warnings] = \local_gugrades\aggregation::get_columns($courseid, $gradecategoryid);

        // Get user aggregation data
        $context = \context_course::instance($courseid);
        $user = \local_gugrades\aggregation::get_user($courseid, $gradecategoryid, $userid);
        $user = \local_gugrades\aggregation::add_aggregation_fields_to_user($courseid, $gradecategoryid, $user, $columns);

        // Do we show the weights?
        $showweights = \local_gugrades\aggregation::show_weights($gradecategoryid);

        // Overriding grade changes explanation.
        if ($user->overridden) {
            $explain = get_string('explain_overridden', 'local_gugrades');
        }

        // Add additional info.
        $user->showweights = $showweights;
        $user->strategy = \local_gugrades\aggregation::get_formatted_strategy($gradecategoryid);
        $user->atype = $atype;
        $user->formattedatype = \local_gugrades\aggregation::translate_atype($atype);
        $user->explain = $explain;

        return $user;
    }

    /**
     * Get aggregation page
     * @param int $courseid
     * @param int $gradecategoryid
     * @param string $firstname
     * @param string $lastname
     * @param int $groupid
     * @param bool $aggregate
     * @return array
     */
    public static function get_aggregation_page(
        int $courseid,
        int $gradecategoryid,
        string $firstname,
        string $lastname,
        int $groupid,
        bool $aggregate
        ) {

        global $CFG, $USER;

        // I know :(
        set_time_limit(0);

        // Is aggregation supported (at all)?
        $gradeitemid = \local_gugrades\grades::get_gradeitemid_from_gradecategoryid($gradecategoryid);
        $aggregationsupported = \local_gugrades\grades::are_all_grades_supported($courseid, $gradeitemid);
        if (!$aggregationsupported) {
            return [
                'aggregationsupported' => $aggregationsupported,
                'toplevel' => false,
                'atype' => '',
                'gradeitemid' => $gradeitemid,
                'strategy' => '',
                'conversion' => '',
                'allowconversion' => false,
                'allowrelease' => false,
                'released' => false,
                'showweights' => false,
                'warnings' => [],
                'columns' => [],
                'users' => [],
                'breadcrumb' => [],
                'excludeempty' => false,
                'debug' => [],
                'staffuserid' => $USER->id,
            ];
        }

        // Cleanup any empty capture page columns.
        // (It's hard to do over on the capture page - trust me).
        \local_gugrades\grades::cleanup_unused_columns_course($courseid);

        // Are we collecting debug information
        $debugon = $CFG->debugdeveloper;
        $timestart = microtime(true);

        // Get the level 1 parent category.
        $level1id = \local_gugrades\grades::get_level_one_parent($gradecategoryid);
        $timelevel1 = microtime(true);

        // Build (and cache) grade structure (whole tree).
        \local_gugrades\aggregation::recurse_tree($courseid, $level1id, $aggregate);
        $timetree = microtime(true);

        // Get categories and items at this level.
        [$columns, $atype, $warnings] = \local_gugrades\aggregation::get_columns($courseid, $gradecategoryid);
        $timecolumns = microtime(true);

        // Don't have duplicate warnings.
        $warnings = array_intersect_key($warnings, array_unique(array_map('serialize', $warnings)));

        // Get all the students.
        $users = \local_gugrades\aggregation::get_users($courseid, $gradecategoryid, $firstname, $lastname, $groupid);
        $timeusers = microtime(true);

        // Recalculate?
        if ($aggregate) {
            \local_gugrades\aggregation::aggregate($courseid, $gradecategoryid, $users);
        }

        // Warning message on top level.
        $istoplevel = \local_gugrades\aggregation::is_top_level($gradecategoryid);

        // Add the columns to the user fields.
        [$users, $addaggdebug] = \local_gugrades\aggregation::add_aggregation_fields_to_users($courseid, $gradecategoryid, $users, $columns);
        $timeaddfields = microtime(true);

        // Get breadcrumb trail.
        $breadcrumb = \local_gugrades\aggregation::get_breadcrumb($gradecategoryid);

        $debug = [];
        if ($debugon) {
            $debug[]['line'] = $timelevel1 - $timestart . ' get level 1';
            $debug[]['line'] = $timetree - $timestart . ' build tree structure';
            $debug[]['line'] = $timecolumns - $timestart . ' get columns';
            $debug[]['line'] = $timeusers - $timestart . ' get users';
            $debug[]['line'] = $timeaddfields - $timestart . ' add fields';
            $debug = array_merge($debug, $addaggdebug);
        }

        // Can we show the conversion controls for this category?d
        // Only available for level 2 categories - MGU-997
        $level = \local_gugrades\grades::get_category_level($gradecategoryid);
        $mapname = \local_gugrades\conversion::get_map_name_for_category($gradecategoryid);
        $allowconversion = ($level == 2) && (!empty($mapname) || ($atype == \local_gugrades\GRADETYPE_POINTS));

        // Allow release. At the moment, this is just going to be "Not points" and "not error".
        $allowrelease = ($atype != \local_gugrades\GRADETYPE_POINTS) && ($atype != \local_gugrades\GRADETYPE_ERROR);

        // Has the aggregated grade been released?
        $released = \local_gugrades\grades::is_grades_released($courseid, $gradeitemid);

        // Do we show the weights?
        $showweights = \local_gugrades\aggregation::show_weights($gradecategoryid);

        // Is 'exclude empty grades' ticked?
        $excludeempty = \local_gugrades\grades::is_exclude_empty_grades($gradecategoryid);

        return [
            'aggregationsupported' => $aggregationsupported,
            'toplevel' => $istoplevel,
            'atype' => $atype,
            'gradeitemid' => $gradeitemid,
            'strategy' => \local_gugrades\aggregation::get_formatted_strategy($gradecategoryid),
            'conversion' => $mapname,
            'allowconversion' => $allowconversion,
            'allowrelease' => $allowrelease,
            'released' => $released,
            'showweights' => $showweights,
            'warnings' => $warnings,
            'columns' => $columns,
            'users' => $users,
            'breadcrumb' => $breadcrumb,
            'excludeempty' => $excludeempty,
            'debug' => $debug,
            'staffuserid' => $USER->id,
        ];
    }

    /**
     * Get aggregation user
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $userid
     * @return object
     */
    public static function get_aggregation_user(int $courseid, int $gradecategoryid, int $userid) {
        global $DB;

        // Get the level 1 parent category.
        $level1id = \local_gugrades\grades::get_level_one_parent($gradecategoryid);

        // (Re-)aggregate this user. Regardless.
        \local_gugrades\aggregation::aggregate_user_helper($courseid, $level1id, $userid);

        // Build (and cache) grade structure (whole tree).
        \local_gugrades\aggregation::recurse_tree($courseid, $level1id, false);

        // Get categories and items at this level.
        [$columns, $atype, $warnings] = \local_gugrades\aggregation::get_columns($courseid, $gradecategoryid);

        // Get user aggregation data
        $context = \context_course::instance($courseid);
        $user = \local_gugrades\aggregation::get_user($courseid, $gradecategoryid, $userid);
        $user = \local_gugrades\aggregation::add_aggregation_fields_to_user($courseid, $gradecategoryid, $user, $columns);

        return $user;
    }

    /**
     * Get user data for dashboard
     * It's basically get_aggregation_user with some extras added
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $userid
     * @return array
     */
    public static function get_aggregation_dashboard_user(int $courseid, int $gradecategoryid, int $userid) {

        // Get basic user field data
        $user = self::get_aggregation_user($courseid, $gradecategoryid, $userid);

        // Run over the fields and add released status.
        foreach ($user->fields as $id => $field) {
            $released = \local_gugrades\grades::is_grades_released($courseid, $field['gradeitemid']);
            $releasegrade = \local_gugrades\grades::get_released_grade($courseid, $field['gradeitemid'], $userid);
            $user->fields[$id]['released'] = $released;
            $user->fields[$id]['releasegrade'] = $releasegrade;
        }

        // Get the category
        $category = \local_gugrades\aggregation::get_enhanced_grade_category($courseid, $gradecategoryid);
        $gradeitemid = $category->itemid;

        // Get provisional grade for the actual category
        $provisional = \local_gugrades\grades::get_provisional_from_id($gradeitemid, $userid);
        $provisional->itemid = $gradeitemid;
        $provisional->released = \local_gugrades\grades::is_grades_released($courseid, $gradeitemid);

        // add the 'parent' grade item to the record
        $user->parent = $provisional;

        return $user;
    }

    /**
     * Resit required
     * @param int $courseid
     * @param int $userid
     * @param bool $resit
     */
    public static function resit_required(int $courseid, int $userid, bool $resit) {
        global $DB;

        if (!$resit) {
            $DB->delete_records('local_gugrades_resitrequired', ['courseid' => $courseid, 'userid' => $userid]);
        } else {
            if (!$DB->record_exists('local_gugrades_resitrequired', ['courseid' => $courseid, 'userid' => $userid])) {
                $resitrequired = new \stdClass();
                $resitrequired->courseid = $courseid;
                $resitrequired->userid = $userid;
                $DB->insert_record('local_gugrades_resitrequired', $resitrequired);
            }
        }
    }

    /**
     * Is mygrades available for given course id?
     * Currently, only checks number of participants
     * @param int $courseid
     * @return bool
     */
    public static function is_mygrades_available(int $courseid) {
        $maxparticipants = get_config('local_gugrades', 'maxparticipants');
        if (empty($maxparticipants)) {
            $maxparticipants = 1200;
        }
        $participants = \local_gugrades\users::count_participants($courseid);

        return $participants <= $maxparticipants;
    }

    /**
     * Check options array for selected state.
     * @param string $gradetype
     * @param string $other
     * @param array $options
     * @return bool
     */
    private static function is_export_option_selected(string $gradetype, array $options) {
        foreach ($options as $option) {
            if ($gradetype != $option['gradetype']) {
                continue;
            }
            return $option['selected'];
        }

        return false;
    }

    /**
     * Return list of selectable columns for capture export
     * Organise as (kind of) fake Gradetypes
     * User preferences will be persisted (TODO)
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @return array
     */
    public static function get_capture_export_options(int $courseid, int $gradeitemid, int $groupid) {

        // Get preferences (if any).
        $pref = get_user_preferences('local_gugrades_captureexportselect');
        if ($pref) {
            $savedoptions = unserialize($pref);
        } else {
            $savedoptions = [];
        }

        // Get fixed options.
        $options = [
            (object)[
                'gradetype' => 'NAME',
                'description' => get_string('name', 'local_gugrades'),
                'other' => '',
            ],
            (object)[
                'gradetype' => 'IDNUMBER',
                'description' => get_string('idnumber', 'local_gugrades'),
                'other' => '',
            ],
            (object)[
                'gradetype' => 'EMAIL',
                'description' => get_string('email'),
                'other' => '',
            ],
            (object)[
                'gradetype' => 'COURSECODE',
                'description' => get_string('coursecode', 'local_gugrades'),
                'other' => '',
            ],
            (object)[
                'gradetype' => 'WARNINGS',
                'description' => get_string('warnings', 'local_gugrades'),
                'other' => '',
            ],
        ];

        // Get columns shown in table for grade types.
        $columns = \local_gugrades\grades::get_grade_capture_columns($courseid, $gradeitemid);
        $options = array_merge($options, $columns);

        // Add selected field for grade types.
        // TODO - needs linked to user preferences.
        foreach ($options as $option) {
            $option->selected = self::is_export_option_selected($option->gradetype, $savedoptions);
        }

        return $options;
    }

    /**
     * Get data for capture export
     * User preferences will be persisted (TODO)
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param bool $viewfullnames
     * @param array $options
     * @return array
     */
    public static function get_capture_export_data(
        int $courseid, int $gradeitemid, int $groupid, bool $viewfullnames, array $options) {

        // Save user's selection.
        set_user_preference('local_gugrades_captureexportselect', serialize($options));

        // Convet options into a simple array of those selected.
        $selected = [];
        foreach ($options as $option) {
            if ($option['selected']) {
                $selected[] = $option['gradetype'];
            }
        }

        // Just get the capture page data. We can filter out what we don't need.
        $page = (object)self::get_capture_page($courseid, $gradeitemid, '', '', $groupid, $viewfullnames);

        // Get the headings.
        $columns = $page->columns;
        $headings = [];
        $gradecolums = [];
        if (in_array('NAME', $selected)) {
            $headings[] = get_string('name', 'local_gugrades');
        }
        if (in_array('IDNUMBER', $selected)) {
            $headings[] = get_string('idnumber', 'local_gugrades');
        }
        if (in_array('EMAIL', $selected)) {
            $headings[] = get_string('email');
        }
        if (in_array('COURSECODE', $selected)) {
            $headings[] = get_string('coursecode', 'local_gugrades');
        }
        if (in_array('WARNINGS', $selected)) {
            $headings[] = get_string('warnings', 'local_gugrades');
        }
        foreach ($columns as $column) {
            $gradetype = $column->gradetype;
            if (in_array($gradetype, $selected)) {
                $headings[] = $column->description;
                $gradecolumns[] = $gradetype;
                if ($gradetype != 'FIRST') {
                    $headings[] = $column->description . ' ' . get_string('notes', 'local_gugrades');
                }
            }
        }

        // Get the data.
        $users = $page->users;
        $data = [$headings];
        foreach ($users as $user) {
            $line = [];
            if (in_array('NAME', $selected)) {
                $line[] = $user->displayname;
            }
            if (in_array('IDNUMBER', $selected)) {
                $line[] = $user->idnumber;
            }
            if (in_array('EMAIL', $selected)) {
                $line[] = $user->email;
            }
            if (in_array('COURSECODE', $selected)) {
                $line[] = \local_gugrades\users::get_course_code($courseid, $user->id);
            }
            if (in_array('WARNINGS', $selected)) {
                $warnings = [];
                if ($user->alert) {
                    $warnings[] = get_string('discrepancy', 'local_gugrades');
                }
                if ($user->gradebookhidden) {
                    $warnings[] = get_string('hiddengradebook', 'local_gugrades');
                }
                if ($user->gradehidden) {
                    $warnings[] = get_string('hiddenmygrades', 'local_gugrades');
                }
                $line[] = implode(', ', $warnings);
            }
            $grades = $user->grades;

            // Run through required data columns looking for data in user->grades array.
            foreach ($gradecolumns as $gradecolumn) {
                $gradetypegrades = array_column($grades, null, 'gradetype'); // Reindex array by field 'gradetype'.
                if (array_key_exists($gradecolumn, $gradetypegrades)) {
                    $grade = $gradetypegrades[$gradecolumn];
                    $line[] = $grade->displaygrade;
                    if ($gradecolumn != 'FIRST') {
                        $line[] = $grade->auditcomment;
                    }
                } else {
                    $line[] = '';
                    if ($gradecolumn != 'FIRST') {
                        $line[] = '';
                    }
                }
            }
            $data[] = $line;
        }

        // Convert array of arrays to csv string.
        $csv = '';
        foreach ($data as $line) {
            $quoted = array_map(function($str) {
                return sprintf('"%s"', $str);
            }, $line);
            $csv .= implode(',', $quoted) . PHP_EOL;
        }

        return $csv;
    }

    /**
     * Recalculate aggregation
     * @param int $courseid
     * @param int $gradecategoryid
     */
    public static function recalculate(int $courseid, int $gradecategoryid) {

        // Clear cache items for this course.
        \local_gugrades\aggregation::invalidate_cache($courseid);

        // Get all the students.
        $users = \local_gugrades\aggregation::get_users($courseid, $gradecategoryid, '', '', 0);

        // Get the level 1 parent category.
        $level1id = \local_gugrades\grades::get_level_one_parent($gradecategoryid);

        // Run over users running aggregation
        \local_gugrades\aggregation::aggregate($courseid, $level1id, $users);
    }

    /**
     * Get the form for altering weights
     * @param int $courseid
     * @param int $categoryid
     * @param int $userid
     * @return array
     */
    public static function get_alter_weight_form(int $courseid, int $categoryid, int $userid) {
        global $DB;

        // Check gradecategory exists.
        // (courseid forces proper check).
        $category = $DB->get_record('grade_categories', ['id' => $categoryid, 'courseid' => $courseid], '*', MUST_EXIST);

        // Get gradeitemid
        $gradeitemid = \local_gugrades\grades::get_gradeitemid_from_gradecategoryid($categoryid);

        // Get the columns for this grade category
        $columns = \local_gugrades\aggregation::get_columns($courseid, $categoryid);

        // Ensure aggregation data for this user is current.
        $user = self::get_aggregation_user($courseid, $categoryid, $userid);

        // Combine required user fields and column data.
        $items = [];
        $userfields = $user->fields;
        foreach ($columns[0] as $id => $column) {
            $field = $userfields[$id];
            [$originalweight, $alteredweight, $isaltered] = \local_gugrades\grades::get_altered_weight($column->gradeitemid, $userid);
            $item = new \stdClass;
            $item->fullname = $column->fullname;
            $item->gradeitemid = $column->gradeitemid;
            $item->gradetype = $column->gradetype;
            $item->display = $field['display'];
            $item->originalweight = $originalweight;
            $item->alteredweight = $alteredweight;
            $item->isaltered = $isaltered;
            $items[$id] = $item;
        }

        return [
            'categoryname' => $category->fullname,
            'userfullname' => fullname($user),
            'idnumber' => $user->idnumber,
            'items' => $items,
        ];
    }

    /**
     * Save altered weights
     * @param int $courseid
     * @param int $categoryid
     * @param int $userid
     * @param bool $revert
     * @param string $reason
     * @param array $settings
     * @return array
     */
    public static function save_altered_weights(int $courseid, int $categoryid, int $userid, bool $revert, string $reason, array $items) {

        // If revert == true then delete the altered grades
        if ($revert) {
            \local_gugrades\grades::revert_altered_weights($courseid, $categoryid, $userid);
        } else {
            foreach ($items as $item) {
                \local_gugrades\grades::update_altered_weight($courseid, $categoryid, $item['gradeitemid'], $userid, $item['weight']);
            }
        }

        // Re-aggregate this user
        \local_gugrades\aggregation::aggregate_user_helper($courseid, $categoryid, $userid);
    }

    /**
     * Get list of aggregation export plugins
     * @param int $courseid
     * @param int $gradecategoryid
     * @return array
     */
    public static function get_aggregation_export_plugins(int $courseid, int $gradecategoryid) {

        return \local_gugrades\export::get_aggregation_export_plugins($courseid, $gradecategoryid);
    }

    /**
     * Get aggregation export form
     * @param int $courseid
     * @param int $gradecategoryid
     * @param string $plugin
     * @return array
     */
    public static function get_aggregation_export_form(int $courseid, int $gradecategoryid, string $plugin) {

        return \local_gugrades\export::get_aggregation_export_form($courseid, $gradecategoryid, $plugin);
    }

    /**
     * Get aggregation export data
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $groupid
     * @param string $plugin
     * @param array $form
     * @return array
     */
    public static function get_aggregation_export_data(int $courseid, int $gradecategoryid, int $groupid, string $plugin, array $form) {

        return \local_gugrades\export::get_aggregation_export_data($courseid, $gradecategoryid, $groupid, $plugin, $form);
    }

    /**
     * Implement get_progress
     * @param int $courseid
     * @param int $uniqueid
     * @param string $progresstype
     * @param int $staffuserid
     * @return int
     */
    public static function get_progress(int $courseid, int $uniqueid, string $progresstype, int $staffuserid) {
        $progress = \local_gugrades\progress::get($courseid, $uniqueid, $progresstype, $staffuserid);

        return [
            'progress' => $progress,
        ];
    }

    /**
     * Get image urls
     * @param int $courseid
     * @param array $images
     * @return array
     */
    public static function get_image_urls(int $courseid, array $images) {
        global $PAGE, $OUTPUT;

        $context = \context_course::instance($courseid);
        $PAGE->set_context($context);

        $urls = [];
        foreach ($images as $image) {
            $urls[] = [
                'url' => $OUTPUT->image_url($image['imagename'], $image['component'])->out(),
            ];
        }

        return $urls;
    }
}
