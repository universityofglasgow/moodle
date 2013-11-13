<?php

class report_anonymous {

    /**
     * get blind assignments for this course
     * @param int $id course id
     * @return array 
     */
    public static function get_assignments($id) {
        global $DB;

        $assignments = $DB->get_records('assign', array('blindmarking'=>1, 'course'=>$id));
        return $assignments;
    }

    /**
     * Get anonymous turnitin assignments and the
     * parts that go with them
     * @param int $id course id
     * @return array 
     */
    public static function get_tts($id) {
        global $DB;

        $tts = $DB->get_records('turnitintool', array('anon'=>1, 'course'=>$id));
        foreach ($tts as $id=>$tt) {
            $parts = $DB->get_records('turnitintool_parts', array('turnitintoolid'=>$id, 'deleted'=>0));
            $tts[$id]->parts = $parts;
        }

        return $tts;
    }

    /**
     * can the user view the data submitted
     * some checks
     * @param string $mod which module (turnitintool or assign)
     * @param int $assignid assignment id
     * @param int $partid turnitintool part id
     * @param array $assignments list of valid assignments
     * @param array $tts list of valid tts
     * @return boolean true if ok
     */
    public static function allowed_to_view($mod, $assignid, $partid, $assignments, $tts) {
        if ($mod=='assign') {
            return array_key_exists($assignid, $assignments);
        } else if ($mod=='turnitintool') {
            foreach ($tts as $tt) {
                if (array_key_exists($partid, $tt->parts)) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /** 
     * Get the list of potential users for the assignment activity
     * @param object $context current role context
     * @return array list of users
     */
    public static function get_assign_users($context) {
        $idsonly = false;
        $currentgroup = null;
        if ($idsonly) {
            return get_enrolled_users($context, "mod/assign:submit", $currentgroup, 'u.id');
        } else {
            return get_enrolled_users($context, "mod/assign:submit", $currentgroup);
        }
    }

    /** 
     * get list of users who have not submitted
     * @param int $assignid assignment id
     * @param array $users list of user objects
     * @return array list of user objects not submitted
     */
    public static function get_assign_notsubmitted($assignid, $users) {
        global $DB;

        $notsubusers = array();
        foreach ($users as $user) {
            if (!$DB->get_record('assign_submission', array('userid'=>$user->id, 'assignment'=>$assignid))) {
                $notsubusers[$user->id] = $user;
            }
        }

        return $notsubusers;
    }

    /**
     * sort users using callback
     */
    public static function sort_users($users, $onname=false) {
        uasort($users, function($a, $b) {
            global $onname;
            if ($onname) {
                return strcasecmp(fullname($a), fullname($b));
            } else {
                return strcasecmp($a->idnumber, $b->idnumber);
            }    
        });
        return $users;
    }
    
    /** 
     * Get the list of potential users for the turnitintool activity
     * @param object $context current role context
     * @return array list of users
     */
    public static function get_turnitintool_users($context) {
        return get_enrolled_users($context, "mod/turnitintool:submit");
    }    

    /** 
     * get list of users who have not submitted
     * @param int $ttid turnitintool id
     * @param int $partid part
     * @param array $users list of user objects
     * @return array list of user objects not submitted
     */
    public static function get_turnitintool_notsubmitted($ttid, $partid, $users) {
        global $DB;

        $notsubusers = array();
        foreach ($users as $user) {
            if (!$DB->get_record('turnitintool_submissions', array('userid'=>$user->id, 'turnitintoolid'=>$ttid, 'submission_part'=>$partid))) {
                $notsubusers[$user->id] = $user;
            }
        }

        return $notsubusers;
    }    
    
}
