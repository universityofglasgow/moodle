<?php
    
    require_once('../../config.php');
    
    $value = required_param('id', PARAM_RAW);
    
    require_login();
    
    $usercontext = context_user::instance($USER->id);
    $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
    
    $ufservice->delete_favourite('core_course', 'courses', $value, \context_course::instance($value));
    
    header('Location: '.$_SERVER['HTTP_REFERER']);
    
?>