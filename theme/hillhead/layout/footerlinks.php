<?php
    
    $footerLinks = Array();
    
    $footerLinks['University Website'] = 'https://www.gla.ac.uk';
    
    $footerLinks['Moodle Mobile App'] = tool_mobile_create_app_download_url();
    
    $footerLinks['Moodle Inspector'] = 'https://moodleinspector.gla.ac.uk';
    
    $footerLinks['Accessibility'] = 'https://www.gla.ac.uk/legal/accessibility/statements/moodle';
    
    $footerLinks['Privacy and Cookies'] = 'https://www.gla.ac.uk/legal/privacy/';
    
    $footerLinkText = '';
    
    foreach ($footerLinks as $name=>$link) {
        $footerLinkText .= '<li><a href="'.$link.'">'.$name.'</a></li>';
    }
    
    $footerLinkText.= '<li class="tool_usertours-resettourcontainer"></li><li>'.page_doc_link('Help with this page').'</li>';
    
    
?>