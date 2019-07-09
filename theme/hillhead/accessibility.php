<?php
    
    require_once('../../config.php');
    
    $setting = required_param('o', PARAM_RAW);
    $value = required_param('v', PARAM_RAW);
    
    require_login();
    
    $allowedPreferences = Array(
        'theme_hillhead_accessibility'  => true,
        'theme_hillhead_contrast'       => true,
        'theme_hillhead_font'           => true,
        'theme_hillhead_bold'           => true,
        'theme_hillhead_spacing'        => true,
        'theme_hillhead_stripstyles'    => true,
        'theme_hillhead_size'           => true,
        'theme_hillhead_readtome'       => true,
        'theme_hillhead_readalert'      => true
    );
    
    if(array_key_exists($setting, $allowedPreferences)) {
        if($value=='clear') {
            unset_user_preference($setting);
        } else {
            set_user_preference($setting, $value);
        }
    } else {
        if($setting == 'theme_hillhead_reset_accessibility') {
            foreach($allowedPreferences as $unset=>$pointlessTrue) {
                if($unset != 'theme_hillhead_accessibility') {
                    unset_user_preference($unset);
                }
            }
        }
    }
    
    
    header('Location: '.$_SERVER['HTTP_REFERER']);
    
?>