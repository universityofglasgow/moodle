<?php
    
    $topButtons = '<div class="btn-toolbar d-flex justify-content-between m-b-1">';
    
    $topButtons .= '<div class="btn-holder">';
        
    $coursecontext = context_course::instance($COURSE->id);
    $usercontext = context_user::instance($USER->id);
    $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
    
    if($ufservice->favourite_exists('core_course', 'courses', $COURSE->id, \context_course::instance($COURSE->id), 0)) {
        $starURL = new moodle_url('/theme/hillhead/course-unstar.php?id='.$COURSE->id);
        $topButtons .= '<a class="btn btn-default m-r-1" href="'.$starURL.'"><i class="fa fa-star"></i> Unstar<span class="d-none d-sm-inline"> This Course</span></a>';
    } else {
        $starURL = new moodle_url('/theme/hillhead/course-star.php?id='.$COURSE->id);
        $topButtons .= '<a class="btn btn-default" href="'.$starURL.'"><i class="fa fa-star-o"></i> Star<span class="d-none d-sm-inline"> This Course</span></a>';
    }
    
    
    $topButtons .= '</div>';
    
    if(has_capability('moodle/course:update', $coursecontext)) {
       
        $topButtons .= '<div class="btn-holder">';
    
        $settingsURL = new moodle_url('/course/edit.php?id='.$COURSE->id);
    
        $topButtons .= '<a class="btn btn-primary m-r-1" href="'.$settingsURL.'"><i class="fa fa-gear"></i> <span class="d-none d-sm-inline">Course </span>Settings</a>';
        
        $editURL = new moodle_url('/course/view.php?id='.$COURSE->id.'&sesskey='.$USER->sesskey);
        
        if($USER->editing) {
            $topButtons .= '<a class="btn btn-success" href="'.$editURL.'&edit=off"><i class="fa fa-pencil"></i> <span class="d-none d-sm-inline">Turn </span>Editing Off</a>';
        } else {
            $topButtons .= '<a class="btn btn-danger" href="'.$editURL.'&edit=on"><i class="fa fa-pencil"></i> <span class="d-none d-sm-inline">Turn </span>Editing On</a>';
        } 
        $topButtons .= '</div>';
    
    }
    $topButtons .= '</div>';
    
?>