<?php

require_once(dirname(__FILE__) . '/config.php');

$rootfrom = required_param('basefrom', PARAM_CLEAN);
$rootto = required_param('baseto', PARAM_CLEAN);

$manager = core_plugin_manager::instance();
$allplugins = $manager->get_plugins();

echo "<ul>";
foreach ($allplugins as $type => $typeplugins) {
    $standard = core_plugin_manager::standard_plugins_list($type);
    foreach ($typeplugins as $plugin => $info) {
	    if (!$standard || !in_array($plugin, $standard)) {
	       if ($info->rootdir) {
	           $from = $rootfrom . $info->rootdir;
	           $to = $rootto . $info->typerootdir;      
		   echo "<li>cp -R $from $to</li>\n";
	       }
       }

    }
}

