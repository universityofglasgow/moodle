<?php

    $breadcrumbLinks = '<ul class="tabBar">';
    
    $breadcrumbLinks .= '<li><a href="'.$CFG->wwwroot.'/my"><i class="fa fa-tachometer"></i> Dashboard</a></li>';
    
    $isOtherPage = false;
    
    if(isset($COURSE) && $COURSE->id != 1) {
        $breadcrumbLinks .= '<li><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'"><i class="fa fa-graduation-cap"></i> '.$COURSE->fullname.'</a></li>';
        if($PAGE->cm) {
            $breadcrumbLinks .= '<li><a href="'.$PAGE->cm->url.'"><i class="fa fa-bookmark"></i> '.$PAGE->cm->name.'</a></li>';
        } else {
            // We're in a course, but not within a module
            $isOtherPage = true;
        }
    } else {
        // We're in some sort of side-wide context
        $isOtherPage = true;
    }
    
    if($isOtherPage) {
        $pageIcon = 'fa-bookmark';
        $pageTitle = $PAGE->title.' ('.$PAGE->pagetype.' - '.$PAGE->subpage.')';
        $pageURL = $PAGE->url;
        $pageShow = true;
        switch($PAGE->pagetype) {
            case 'my-index':
            case 'course-view-topics':
            $pageShow = false;
                break;
            case 'admin-index':
                $pageIcon = 'fa-gear';
                $pageTitle = 'Site Administration';
                break;
            case 'admin-tool-lp-coursecompetencies':
                $pageIcon = 'fa-check-square-o';
                $pageTitle = 'Competencies';
                break;
            case 'badges-view':
                $pageIcon = 'fa-shield';
                $pageTitle = 'Badges';
                break;
            case 'calendar-view':
                $pageIcon = 'fa-calendar';
                $pageTitle = 'Calendar';
                break;
            case 'grade-report-grader-index':
                $pageIcon = 'fa-table';
                $pageTitle = 'Grades';
                break;
            case 'user-files':
                $pageIcon = 'fa-file-o';
                $pageTitle = 'Private Files';
                break;
        }
        
        if($pageShow) {
            $breadcrumbLinks .= '<li><a href="'.$pageURL.'"><i class="fa '.$pageIcon.'"></i> '.$pageTitle.'</a></eli>';
        }
    }
    
    
    
    $hillheadHelpLink = get_config('theme_hillhead', 'hillhead_helpcentre');
    if(!empty($hillheadHelpLink)) {
        $breadcrumbLinks .= '<li class="help"><a href="'.$hillheadHelpLink.'" title="Help with Moodle"><i class="fa fa-question-circle"></i> Help</a></li>';
    }
    
    $breadcrumbLinks .= '</ul>';
    
?>