<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// libraries & acl
require_once "common.php";

//known languages
$languages = array("it", "en");

//Fallback--> english
$path_base = 'help' . DIRECTORY_SEPARATOR . 'help_en.php';

//prepare all the paths, both the base and the specific for the current user
if (isset($SESSION->lang) && in_array($SESSION->lang, $languages)) {
    $path = 'help' . DIRECTORY_SEPARATOR . 'help_' . ($SESSION->lang) . '.php';
} else {
    $path = $path_base;
}

//var_dump($path);
//check if the help in the current lang exists and is available to be included, otherwise use the fallback....
if (!@include_once($path)) {
    print('<i>[[Sorry, the help is not yet localized in your native language, for doing it you have to provide a translated version of the file ' . __DIR__ . DIRECTORY_SEPARATOR . $path . ']]</i>');
    include_once ($path_base);
}
?>