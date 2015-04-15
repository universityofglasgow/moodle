<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gismo;

// this class is used to fetch static data
class FetchStaticDataMoodle {

    // course data
    protected $id;
    protected $timecreated;
    protected $fullname;
    protected $course;
    // actor
    protected $actor;
    // analysis start date / time
    protected $start_date;
    protected $start_time;
    // analysis end date / time
    protected $end_date;
    protected $end_time;
    // useful fields
    protected $users_ids;
    protected $teachers_ids;
    // Json fields
    protected $users;
    protected $teachers;
    protected $resources;
    protected $assignments; //Added 4.10.2013 because it was missing
    protected $assignments22;
    protected $chats;
    protected $forums;
    protected $quizzes;
    protected $wikis;

    // constructor
    public function __construct($id, $actor) {
        $this->id = $id;
        $this->actor = $actor;
    }

    // getter
    public function __get($name) {
        return (property_exists($this, $name)) ? $this->$name : null;
    }

    // init
    public function init() {
        // check variable
        $check = true;
        // fetch data
        $check &= $this->FetchInfo();
        $check &= $this->FetchUsers();
        $check &= $this->FetchTeachers();
        $check &= $this->FetchResources();
        $check &= $this->FetchAssignments();
        $check &= $this->FetchAssignments22();
        $check &= $this->FetchChats();
        $check &= $this->FetchForums();
        $check &= $this->FetchQuizzes();
        $check &= $this->FetchWikis();
        // start date / time
        $check &= $this->FetchStartDateAndTime();
        // return result
        return $check;
    }

    // fetch course info
    protected function FetchInfo() {
        global $DB;
        // check variable
        $check = true;
        // fetch course
        $record = $DB->get_record("course", array("id" => $this->id));
        // save data
        if ($record !== FALSE) {
            $this->timecreated = $record->timecreated;
            $this->fullname = $record->fullname;
            $this->course = $record;
        } else {
            $check = false;
        }
        // return result
        return $check;
    }

    // fetch users
    protected function FetchUsers() {
        global $USER;
        // default variables
        $check = false;
        $this->users = "[]";
        switch ($this->actor) {
            case "teacher":
                // fetch students
                $context = \context_course::instance($this->course->id);
                if ($context !== FALSE) {
                    $users = get_users_by_capability($context, "block/gismo:trackuser", "", "lastname, firstname");
                    // save data
                    if ($users !== FALSE) {
                        $json_users = array();
                        $check = true;
                        if (is_array($users) AND count($users) > 0) {
                            foreach ($users as $user) {
                                $json_users[] = array("id" => $user->id,
                                    "name" => ucfirst($user->lastname) . " " . ucfirst($user->firstname),
                                    "visible" => "1");
                            }
                            $this->users = json_encode($json_users);
                            $this->users_ids = array_keys($users);
                        }
                    }
                }
                break;
            default:
                $json_users = array();
                $json_users[] = array("id" => $USER->id,
                    "name" => ucfirst($USER->lastname) . " " . ucfirst($USER->firstname),
                    "visible" => "1");
                $this->users = json_encode($json_users);
                $this->users_ids = array($USER->id);
                break;
        }
        // return result
        return $check;
    }

    // fetch teachers
    protected function FetchTeachers() {
        // default variables
        $check = false;
        $this->teachers = "[]";
        switch ($this->actor) {
            case "teacher":
                // fetch teachers
                $context = \context_course::instance($this->course->id);
                if ($context !== FALSE) {
                    $teachers = get_users_by_capability($context, "block/gismo:trackteacher", "", "lastname, firstname");
                    // save data
                    if ($teachers !== FALSE) {
                        $json_teachers = array();
                        $check = true;
                        if (is_array($teachers) AND count($teachers) > 0) {
                            foreach ($teachers as $teacher) {
                                $json_teachers[] = array("id" => $teacher->id,
                                    "name" => ucfirst($teacher->lastname) . " " . ucfirst($teacher->firstname),
                                    "visible" => "1");
                            }
                            $this->teachers = json_encode($json_teachers);
                            $this->teachers_ids = array_keys($teachers);
                        }
                    }
                }
                break;
            default:
                break;
        }
        // return result
        return $check;
    }

    // fetch course modules ordered by position
    protected function FetchCourseModulesOrderedByPosition($modulenames, $course, $userid, $includeinvisible, $orderbytype = false) {
        $ordered_modules = array();
        if (is_array($modulenames) AND count($modulenames) > 0) {
            $modules = array();
            // extract modules instances specified in $modulenames
            $tmp_modules = array();
            foreach ($modulenames as $m) {
                $tmp = get_all_instances_in_course($m, $course, $userid, $includeinvisible);
                //Order by name
                //Sort list by name
                usort($tmp, array('\block_gismo\GISMOutil', 'sort_function'));

                if (is_array($tmp) AND count($tmp) > 0) {
                    foreach ($tmp as $t) {
                        $reduced = array_intersect_key((array) $t, array("coursemodule" => "", "id" => "", "course" => "", "name" => "", "visible" => ""));
                        $reduced["type"] = $m;
                        array_push($tmp_modules, (object) $reduced);
                    }
                }
                unset($tmp);
            }
            // sort modules instances by position
            if (is_array($tmp_modules) AND count($tmp_modules) > 0) {
                // MOODLE BUG (get_all_instances_in_course doesn't return an array indexed by cm.id) START
                foreach ($tmp_modules as $tm) {
                    $modules[$tm->coursemodule] = $tm;
                }
                unset($tmp_modules);

                //If orderbytype (resources) return the list ordered by name
                if ($orderbytype) {
                    return $modules;
                }

                // MOODLE BUG (get_all_instances_in_course doesn't return an array indexed by cm.id) END
                //$sections = get_all_sections($this->id); //DEPRECATED 2 new lines added
                $modinfo = get_fast_modinfo($course);
                $sections = $modinfo->get_section_info_all();
                if (is_array($sections) AND count($sections) > 0) {
                    foreach ($sections as $s) {
                        if (!is_null($s->sequence)) {
                            $sequences = explode(",", $s->sequence);
                            if (is_array($sequences) AND count($sequences) > 0) {
                                foreach ($sequences as $sq) {
                                    if (array_key_exists($sq, $modules)) {
                                        $ordered_modules[$sq] = $modules[$sq];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $ordered_modules;
    }

    // fetch resources
    protected function FetchResources() {
        global $USER;
        // default variables
        $check = false;
        $this->resources = "[]";
        // fetch resources
        $resources = $this->FetchCourseModulesOrderedByPosition(array("book", "folder", "imscp", "page", "resource", "url"), $this->course, $USER->id, true, true); //Added book
        // save data
        if ($resources !== FALSE) {
            $json_resources = array();
            $check = true;
            if (is_array($resources) AND count($resources) > 0) {
                foreach ($resources as $resource) {
                    $json_resources[] = array(
                        "id" => $resource->id,
                        "name" => $resource->name,
                        "visible" => $resource->visible,
                        "type" => $resource->type
                    );
                }
                $this->resources = json_encode($json_resources);
            }
        }
        // return result
        return $check;
    }

    // fetch assignments
    protected function FetchAssignments() {
        global $USER;
        // default variables
        $check = false;
        $this->assignments = "[]";
        // fetch assignments
        $assignments = get_all_instances_in_course("assign", $this->course, null, true);
        // $assignments = $this->FetchCourseModulesOrderedByPosition("assignment", $this->course, $USER->id, true);
        // save data
        if ($assignments !== FALSE) {
            $json_assignments = array();
            $check = true;
            if (is_array($assignments) AND count($assignments) > 0) {
                foreach ($assignments as $assignment) {
                    $json_assignments[] = array(
                        "id" => $assignment->id,
                        "name" => $assignment->name,
                        "allowsubmissionsfromdate" => $assignment->allowsubmissionsfromdate,
                        "gradeOver" => $assignment->grade,
                        "duedate" => $assignment->duedate,
                        "visible" => $assignment->visible
                    );
                }
                $this->assignments = json_encode($json_assignments);
            }
        }
        // return result
        return $check;
    }

    // fetch assignments22
    protected function FetchAssignments22() {
        global $USER;
        // default variables
        $check = false;
        $this->assignments22 = "[]";
        // fetch assignments22
        $assignments22 = get_all_instances_in_course("assignment", $this->course, null, true);
        // $assignments22 = $this->FetchCourseModulesOrderedByPosition("assignment", $this->course, $USER->id, true);
        // save data
        if ($assignments22 !== FALSE) {
            $json_assignments22 = array();
            $check = true;
            if (is_array($assignments22) AND count($assignments22) > 0) {
                foreach ($assignments22 as $assignment) {
                    $json_assignments22[] = array(
                        "id" => $assignment->id,
                        "name" => $assignment->name,
                        "timeavailable" => $assignment->timeavailable,
                        "gradeOver" => $assignment->grade,
                        "timedue" => $assignment->timedue,
                        "visible" => $assignment->visible
                    );
                }
                $this->assignments22 = json_encode($json_assignments22);
            }
        }
        // return result
        return $check;
    }

    // fetch chats
    protected function FetchChats() {
        global $USER;
        // default variables
        $check = false;
        $this->chats = "[]";
        // fetch chats
        $chats = get_all_instances_in_course("chat", $this->course, null, true);
        // $chats = $this->FetchCourseModulesOrderedByPosition("chat", $this->course, $USER->id, true);
        // save data
        if (is_array($chats) AND count($chats) > 0) {
            $json_chats = array();
            $check = true;
            foreach ($chats as $chat) {
                $json_chats[] = array(
                    "id" => $chat->id,
                    "name" => $chat->name,
                    "visible" => $chat->visible
                );
            }
            $this->chats = json_encode($json_chats);
        }
        // return result
        return $check;
    }

    // fetch forums
    protected function FetchForums() {
        global $USER;
        // default variables
        $check = false;
        $this->forums = "[]";
        // fetch forums
        $forums = get_all_instances_in_course("forum", $this->course, null, true);
        // $forums = $this->FetchCourseModulesOrderedByPosition("forum", $this->course, $USER->id, true);
        // save data
        if (is_array($forums) AND count($forums) > 0) {
            $json_forums = array();
            $check = true;
            foreach ($forums as $forum) {
                $json_forums[] = array(
                    "id" => $forum->id,
                    "name" => $forum->name,
                    "visible" => $forum->visible
                );
            }
            $this->forums = json_encode($json_forums);
        }
        // return result
        return $check;
    }

    // fetch quizzes
    protected function FetchQuizzes() {
        global $USER;
        // default variables
        $check = false;
        $this->quizzes = "[]";
        // fetch quizzes
        $quizzes = get_all_instances_in_course("quiz", $this->course, null, true);
        // $quizzes = $this->FetchCourseModulesOrderedByPosition("quiz", $this->course, $USER->id, true);
        // save data
        if ($quizzes !== FALSE) {
            $json_quizzes = array();
            $check = true;
            if (is_array($quizzes) AND count($quizzes) > 0) {
                foreach ($quizzes as $quiz) {
                    $json_quizzes[] = array(
                        "id" => $quiz->id,
                        "name" => $quiz->name,
                        "timeopen_qui" => $quiz->timeopen,
                        "timeclose_qui" => $quiz->timeclose,
                        "visible" => $quiz->visible
                    );
                }
                $this->quizzes = json_encode($json_quizzes);
            }
        }
        // return result
        return $check;
    }

    // fetch wikis
    protected function FetchWikis() {
        global $USER;
        // default variables
        $check = false;
        $this->wikis = "[]";
        // fetch wikis
        $wikis = get_all_instances_in_course("wiki", $this->course, null, true);
        // $wikis = $this->FetchCourseModulesOrderedByPosition("wiki", $this->course, $USER->id, true);
        // save data
        if (is_array($wikis) AND count($wikis) > 0) {
            $json_wikis = array();
            $check = true;
            foreach ($wikis as $wiki) {
                $json_wikis[] = array(
                    "id" => $wiki->id,
                    "name" => $wiki->name,
                    "visible" => $wiki->visible
                );
            }
            $this->wikis = json_encode($json_wikis);
        }
        // return result
        return $check;
    }

    // fetch start date and time
    protected function FetchStartDateAndTime() {
        global $DB, $CFG;

        // check variable
        $check = true;

        // select min date / time & max date / time for each log table
        // default
        $this->end_time = time();
        $this->end_date = date("Y-m-d", $this->end_time);
        $this->start_time = $this->end_time - ((empty($CFG->loglifetime)) ? 86400 : $CFG->loglifetime * 86400);
        $this->start_date = date("Y-m-d", $this->start_time);

        // adjust values according to logs
        if (is_array($this->users_ids) AND count($this->users_ids) > 0) {
            // useful data for queries
            $tables = array("block_gismo_activity", "block_gismo_resource", "block_gismo_sl");
            list($userid_sql, $params) = $DB->get_in_or_equal($this->users_ids);

            // push to the params array the course id
            array_push($params, $this->id);

            // get the lowest date & time from the gismo tables and adjust START date and time
            $time = null;
            $date = null;
            foreach ($tables as $table) {
                $tmp = $DB->get_records_select($table, "userid $userid_sql AND course = ?", $params, "time ASC", "id, time, timedate", 0, 1);
                if (is_array($tmp) AND count($tmp) > 0) {
                    $tmp = array_pop($tmp);
                    $time = (is_null($time) OR $tmp->time < $time) ? $tmp->time : $time;
                    $date = (is_null($date) OR $tmp->timedate < $date) ? $tmp->timedate : $date;
                }
            }
            if (!(is_null($time) AND is_null($date))) {
                $this->start_time = $time;
                $this->start_date = $date;
            }

            // get the highest date & time from the gismo tables and adjust END date and time
            $time = null;
            $date = null;
            foreach ($tables as $table) {
                $tmp = $DB->get_records_select($table, "userid $userid_sql AND course = ?", $params, "time DESC", "id, time, timedate", 0, 1);
                if (is_array($tmp) AND count($tmp) > 0) {
                    $tmp = array_pop($tmp);
                    $time = (is_null($time) OR $tmp->time > $time) ? $tmp->time : $time;
                    $date = (is_null($date) OR $tmp->timedate > $date) ? $tmp->timedate : $date;
                }
            }
            if (!(is_null($time) AND is_null($date))) {
                $this->end_time = $time;
                $this->end_date = $date;
            }

            // start date & time => to the first day of the month
            $this->start_time = \block_gismo\GISMOutil::this_month_first_day_time($this->start_time);
            $this->start_date = date("Y-m-d", $this->start_time);

            // end date & time => to the first day of the next month
            $this->end_time = \block_gismo\GISMOutil::next_month_first_day_time($this->end_time);
            $this->end_date = date("Y-m-d", $this->end_time);
        }
        // return result
        return $check;
    }

    public function checkData() {
        return ($this->checkUsers() AND ( $this->checkResources() OR $this->checkActivities())) ? true : false;
    }

    public function checkUsers() {
        return ($this->users !== "[]") ? true : false;
    }

    public function checkTeachers() {
        return ($this->users !== "[]") ? true : false;
    }

    public function checkResources() {
        return ($this->resources !== "[]") ? true : false;
    }

    public function checkActivities() {
        return ($this->assignments !== "[]" OR $this->assignments22 !== "[]" OR $this->chats !== "[]" OR $this->forums !== "[]" OR $this->quizzes !== "[]" OR $this->wikis !== "[]") ? true : false;
    }

}

?>