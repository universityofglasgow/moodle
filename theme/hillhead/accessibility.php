<?php
    
    require_once('../../config.php');
    
    $setting = required_param('o', PARAM_RAW);
    $value = required_param('v', PARAM_RAW);
    
    require_login();
    
    if($value=='clear') {
        unset_user_preference($setting);
    } else {
        set_user_preference($setting, $value);
    }
    
    header('Location: '.$_SERVER['HTTP_REFERER']);
    
?>