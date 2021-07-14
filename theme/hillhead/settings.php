<?php
 
// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.
 
// This line protects the file from being accessed by a URL directly.                                                               
defined('MOODLE_INTERNAL') || die();                                                                                                
 
// This is used for performance, we don't need to know about these settings on every page in Moodle, only when                      
// we are looking at the admin settings pages.                                                                                      
if ($ADMIN->fulltree) {                                                                                                             
 
    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.                         
    $settings = new theme_boost_admin_settingspage_tabs('themesettinghillhead', get_string('configtitle', 'theme_hillhead'));             
 
    // Each page is a tab - the first is the "General" tab.                                                                         
    $page = new admin_settingpage('theme_hillhead_general', get_string('generalsettings', 'theme_hillhead'));                             
 
    // Replicate the preset setting from boost.                                                                                     
    $name = 'theme_hillhead/preset';                                                                                                   
    $title = get_string('preset', 'theme_hillhead');                                                                                   
    $description = get_string('preset_desc', 'theme_hillhead');                                                                        
    $default = 'default.scss';                                                                                                      
 
    // We list files in our own file area to add to the drop down. We will provide our own function to                              
    // load all the presets from the correct paths.                                                                                 
    $context = context_system::instance();                                                                                          
    $fs = get_file_storage();                                                                                                       
    $files = $fs->get_area_files($context->id, 'theme_hillhead', 'preset', 0, 'itemid, filepath, filename', false);                    
 
    $choices = [];                                                                                                                  
    foreach ($files as $file) {                                                                                                     
        $choices[$file->get_filename()] = $file->get_filename();                                                                    
    }                                                                                                                               
    // These are the built in presets from Boost.                                                                                   
    $choices['default.scss'] = 'default.scss';                                                                                      
    $choices['plain.scss'] = 'plain.scss';                                                                                          
 
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                     
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Preset files setting.                                                                                                        
    $name = 'theme_hillhead/presetfiles';                                                                                              
    $title = get_string('presetfiles','theme_hillhead');                                                                               
    $description = get_string('presetfiles_desc', 'theme_hillhead');                                                                   
 
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,                                         
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));                                                               
    $page->add($setting);     
 
    // Variable $brand-color.                                                                                                       
    // We use an empty default value because the default colour should come from the preset.                                        
    $name = 'theme_hillhead/brandcolor';                                                                                               
    $title = get_string('brandcolor', 'theme_hillhead');                                                                               
    $description = get_string('brandcolor_desc', 'theme_hillhead');                                                                    
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');                                               
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);                                                                                                          
 
    // Advanced settings.                                                                                                           
    $page = new admin_settingpage('theme_hillhead_notifications', get_string('notificationsettings', 'theme_hillhead'));      
    
    $name = 'theme_hillhead/hillhead_smart_alerts';                                                                                                   
    $title = get_string('hillhead_smart_alerts', 'theme_hillhead');                                                                                   
    $description = get_string('hillhead_smart_alerts_desc', 'theme_hillhead');
    
    $choices = Array(
        'enabled' => get_string('hillhead_smart_alerts_on', 'theme_hillhead'),
        'disabled' => get_string('hillhead_smart_alerts_off', 'theme_hillhead')
    );                                                                     
    $default = 'disabled';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                                                                                                                                                                     
    $page->add($setting);
    
    $name = 'theme_hillhead/hillhead_old_browser_alerts';                                                                                                   
    $title = get_string('hillhead_old_browser_alerts', 'theme_hillhead');                                                                                   
    $description = get_string('hillhead_old_browser_alerts_desc', 'theme_hillhead');
    
    $choices = Array(
        'enabled' => get_string('hillhead_old_browser_alerts_on', 'theme_hillhead'),
        'disabled' => get_string('hillhead_old_browser_alerts_off', 'theme_hillhead')
    );                                                                     
    $default = 'disabled';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                                                                                                                                                                     
    $page->add($setting);
    
    $name = 'theme_hillhead/hillhead_notification_type';                                                                                                   
    $title = get_string('hillhead_notification_type', 'theme_hillhead');                                                                                   
    $description = get_string('hillhead_notification_type_desc', 'theme_hillhead');
    
    $choices = Array(
        'alert-none' => get_string('hillhead_notification_none', 'theme_hillhead'),
        'alert-danger' => get_string('hillhead_notification_danger', 'theme_hillhead'),
        'alert-warning' => get_string('hillhead_notification_warning', 'theme_hillhead'),
        'alert-success' => get_string('hillhead_notification_success', 'theme_hillhead'),
        'alert-info' => get_string('hillhead_notification_info', 'theme_hillhead')
    );                                                                     
    $default = 'alert-none';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                                                                                                                                                                    
    $page->add($setting);
 
    // Custom System Notification                                                                                   
    $setting = new admin_setting_configtextarea('theme_hillhead/hillhead_notification',                                                              
        get_string('hillhead_notification', 'theme_hillhead'), get_string('hillhead_notification_desc', 'theme_hillhead'), '', PARAM_RAW);                                                                                            
    $page->add($setting);
 
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);  
    
    $page = new admin_settingpage('theme_hillhead_help', get_string('helpsettings', 'theme_hillhead'));
 
    $setting = new admin_setting_configtext('theme_hillhead/hillhead_helpcentre', get_string('helplink', 'theme_hillhead'),                           
        get_string('helplink_desc', 'theme_hillhead'), '', PARAM_RAW);                                                                                                                                        
    $page->add($setting); 
 
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);  
 
    // Advanced settings.                                                                                                           
    $page = new admin_settingpage('theme_hillhead_advanced', get_string('advancedsettings', 'theme_hillhead'));                           
 
    // Raw SCSS to include before the content.                                                                                      
    $setting = new admin_setting_configtextarea('theme_hillhead/scsspre',                                                              
        get_string('rawscsspre', 'theme_hillhead'), get_string('rawscsspre_desc', 'theme_hillhead'), '', PARAM_RAW);                      
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Raw SCSS to include after the content.                                                                                       
    $setting = new admin_setting_configtextarea('theme_hillhead/scss', get_string('rawscss', 'theme_hillhead'),                           
        get_string('rawscss_desc', 'theme_hillhead'), '', PARAM_RAW);                                                                  
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Custom System Notification Theme
    
    
    $setting = new admin_setting_configtextarea('theme_hillhead/login_intro',                                                              
    get_string('login_intro', 'theme_hillhead'), get_string('login_intro_desc', 'theme_hillhead'), '', PARAM_RAW);                                                                                            
    $page->add($setting);   
 
    $settings->add($page);                                                                                                          
}