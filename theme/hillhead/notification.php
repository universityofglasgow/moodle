<?php
    
    require_once('../../config.php');
    
    $notificationHash = required_param('h', PARAM_RAW);
    
    require_login();
    
    if(!isset($_SESSION['SESSION']->hillhead_notifications)) {
        $_SESSION['SESSION']->hillhead_notifications = Array();
    }
    $_SESSION['SESSION']->hillhead_notifications[$notificationHash] = 1;
    
    header('Location: '.$_SERVER['HTTP_REFERER']);
    
?>