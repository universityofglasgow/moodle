<?php
require_once("../../../../../config.php");

/** TinyMCE doesn't allow for customised icons in the theme, but we need this, so look for one manually **/
$custom_path = $CFG->dirroot."/theme/".$CFG->theme."/pix_plugins/tinymce/helixmedia/img/helix_grey.png";

if (file_exists($custom_path))
{
 echo $CFG->wwwroot."/theme/".$CFG->theme."/pix_plugins/tinymce/helixmedia/img/helix_grey.png";
}
else
{
 echo $CFG->wwwroot."/lib/editor/tinymce/plugins/helixmedia/tinymce/img/helix_grey.png";
}
