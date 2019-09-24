<?php
    
    if(isloggedin()) {
        
        global $DB;
    
        $usercontext = context_user::instance($USER->id);
        
        $userservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
        
        $favourites = $userservice->find_favourites_by_type('core_course', 'courses', 0, 15);
        
        usort($favourites, function($a, $b) {
            if ($a->ordering == $b->ordering) return 0;
            return ($a->ordering > $b->ordering) ? -1 : 1;
        });
        
        foreach($favourites as $favourite) {
            
            if($thisCourseDetails = $DB->get_record('course', array('id' => $favourite->itemid), '*')) {
                $thisCourseLink = new moodle_url('/course/view.php?id='.$favourite->itemid);
                $thisCourseNav = navigation_node::create($thisCourseDetails->fullname, $thisCourseLink);
                $thisCourseFlat = new flat_navigation_node($thisCourseNav, 0);
                $thisCourseFlat->key = 'starredcourse-'.$favourite->itemid;
                $thisCourseFlat->icon = new pix_icon('hillhead/starred', $thisCourseDetails->fullname, 'moodle');
                $thisCourseFlat->type = 69;
                $PAGE->flatnav->add($thisCourseFlat);
            }
        }
        
    }

?>