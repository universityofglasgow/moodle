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
    $choices['blue.scss'] = 'University Blue';                                                                                      
    $choices['green.scss'] = 'Moss Green';
    $choices['red.scss'] = 'Pillarbox Red';
    $choices['grey.scss'] = 'Slate Grey';
    $choices['brown.scss'] = 'Sandstone Brown';
    $choices['orange.scss'] = 'Rust Orange';
    $choices['purple.scss'] = 'Lavender Purple';                                                                                             
 
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                     
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Preset files setting.                                                                                                        
    $name = 'theme_hillhead/presetfiles';                                                                                              
    $title = get_string('presetfiles','theme_hillhead');                                                                               
    $description = get_string('presetfiles_desc', 'theme_hillhead');                                                                                                                                                                       
 
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);                                                                                                          
 
    $page = new admin_settingpage('theme_hillhead_sidebar', get_string('sidebar', 'theme_hillhead'));
 
    $setting = new admin_setting_configtext('theme_hillhead/hillhead_globalpinned_heading',                                                              
        get_string('hillhead_globalpinned_heading', 'theme_hillhead'), get_string('hillhead_globalpinned_heading_desc', 'theme_hillhead'), 'Important Resources', PARAM_RAW);                                                                                            
    $page->add($setting);
 
    $setting = new admin_setting_configtextarea('theme_hillhead/hillhead_globalpinned',                                                              
        get_string('hillhead_globalpinned', 'theme_hillhead'), get_string('hillhead_globalpinned_desc', 'theme_hillhead'), '', PARAM_RAW);                                                                                            
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
    
    $name = 'theme_hillhead/hillhead_student_course_alert';                                                                                                   
    $title = get_string('hillhead_student_course_alert', 'theme_hillhead');                                                                                   
    $description = get_string('hillhead_student_course_alert_desc', 'theme_hillhead');
    
    $choices = Array(
        'enabled' => get_string('hillhead_student_course_alert_on', 'theme_hillhead'),
        'disabled' => get_string('hillhead_student_course_alert_off', 'theme_hillhead')
    );                                                                     
    $default = 'disabled';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                                                                                                                                                                     
    $page->add($setting);
    
    $setting = new admin_setting_configtextarea('theme_hillhead/hillhead_student_course_alert_text',                                                              
        get_string('hillhead_student_course_alert_text', 'theme_hillhead'), get_string('hillhead_student_course_alert_text_desc', 'theme_hillhead'), '<p>Most lecturers make their courses available to students within the first week of a new semester. However, sometimes lecturers need a bit of time to update the material in their courses or to take backups of last year\'s students\' work. There might be a delay in these cases.</p><p>If you can\'t see something you expect to see in Moodle, it\'s best to double check with your lecturer. They\'re the ones who decide which courses you have access to. <strong>The IT Helpdesk can\'t add you to courses.</strong></p>', PARAM_RAW);                                                                                            
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
    
    $setting = new admin_setting_configtext('theme_hillhead/hillhead_downtime_datetime',                                                              
        get_string('hillhead_downtime_datetime', 'theme_hillhead'), get_string('hillhead_downtime_datetime_desc', 'theme_hillhead'), '2021-02-08 00:00:00', PARAM_RAW);                                                                                            
    $page->add($setting);
    
    $name = 'theme_hillhead/hillhead_downtime_length';                                                                                                   
    $title = get_string('hillhead_downtime_length', 'theme_hillhead');                                                                                   
    $description = get_string('hillhead_downtime_length_desc', 'theme_hillhead');
    
    $choices = Array(
        '30'  => get_string('hillhead_downtime_length_30', 'theme_hillhead'),
        '60'  => get_string('hillhead_downtime_length_60', 'theme_hillhead'),
        '90'  => get_string('hillhead_downtime_length_90', 'theme_hillhead'),
        '120' => get_string('hillhead_downtime_length_120', 'theme_hillhead'),
        '180' => get_string('hillhead_downtime_length_180', 'theme_hillhead'),
        '240' => get_string('hillhead_downtime_length_240', 'theme_hillhead'),
        '480' => get_string('hillhead_downtime_length_480', 'theme_hillhead'),
    );                                                                     
    $default = '180';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                                                                                                                                                                    
    $page->add($setting);
 
    // Custom System Notification                                                                                   
    /*$setting = new admin_setting_configtextarea('theme_hillhead/hillhead_notification',                                                              
        get_string('hillhead_notification', 'theme_hillhead'), get_string('hillhead_notification_desc', 'theme_hillhead'), '', PARAM_RAW);                                                                                            
    $page->add($setting);
 
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);  
    
    $page = new admin_settingpage('theme_hillhead_help', get_string('helpsettings', 'theme_hillhead'));
 
    $setting = new admin_setting_configtext('theme_hillhead/hillhead_helpcentre', get_string('helplink', 'theme_hillhead'),                           
        get_string('helplink_desc', 'theme_hillhead'), '', PARAM_RAW);                                                                                                                                        
    $page->add($setting); */
 
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