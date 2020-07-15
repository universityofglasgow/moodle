<?php
    
    require_once('../../config.php');
    
    $value = required_param('o', PARAM_RAW);
    
    require_login();
    
    set_user_preference('theme_hillhead_starorder', $value);
    
?>