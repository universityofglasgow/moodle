<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a light box of the section.
 *
 * @package    course/format
 * @subpackage grid
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com, about.me/gjbarnard and {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['display_summary'] = 'move out of grid';
$string['display_summary_alt'] = 'Move this section out of the grid';
$string['editimage'] = 'Change image';
$string['editimage_alt'] = 'Set or change image';
$string['formatgrid'] = 'Grid format'; // Name to display for format.
$string['general_information'] = 'General Information';  // No longer used kept for legacy versions.
$string['hidden_topic'] = 'This section has been hidden';
$string['hide_summary'] = 'move section into grid';
$string['hide_summary_alt'] = 'Move this section into the grid';
$string['namegrid'] = 'Grid view';
$string['title'] = 'Section title';
$string['topic'] = 'Section';
$string['topic0'] = 'General';
$string['topicoutline'] = 'Section';  // No longer used kept for legacy versions.

// Moodle 2.0 Enhancement - Moodle Tracker MDL-15252, MDL-21693 & MDL-22056 - http://docs.moodle.org/en/Development:Languages.
$string['sectionname'] = 'Section';
$string['pluginname'] = 'Grid format';
$string['section0name'] = 'General';

// WAI-ARIA - http://www.w3.org/TR/wai-aria/roles.
$string['gridimagecontainer'] = 'Grid images';
$string['closeshadebox'] = 'Close shade box';
$string['previoussection'] = 'Previous section';
$string['nextsection'] = 'Next section';
$string['shadeboxcontent'] = 'Shade box content';

// MDL-26105.
$string['page-course-view-grid'] = 'Any course main page in the grid format';
$string['page-course-view-grid-x'] = 'Any course page in the grid format';

// Moodle 2.3 Enhancement.
$string['hidefromothers'] = 'Hide section'; // No longer used kept for legacy versions.
$string['showfromothers'] = 'Show section'; // No longer used kept for legacy versions.
$string['currentsection'] = 'This section'; // No longer used kept for legacy versions.
$string['markedthissection'] = 'This section is highlighted as the current section';
$string['markthissection'] = 'Highlight this section as the current section';

// Moodle 2.4 Course format refactoring - MDL-35218.
$string['numbersections'] = 'Number of sections';

// Exception messages.
$string['imagecannotbeused'] = 'Image cannot be used, must be a PNG, JPG or GIF and the GD PHP extension must be installed.';
$string['cannotfinduploadedimage'] = 'Cannot find the uploaded original image.  Please report error details to developer.';
$string['cannotconvertuploadedimagetodisplayedimage'] = 'Cannot convert uploaded image to displayed image.  Please report error details to developer.';
$string['cannotgetimagesforcourse'] = 'Cannot get images for course.  Please report error details to developer.';

// CONTRIB-4099 Image container size change improvement.
$string['off'] = 'Off';
$string['on'] = 'On';
$string['scale'] = 'Scale';
$string['crop'] = 'Crop';
$string['imagefile'] = 'Upload an image';
$string['imagefile_help'] = 'Upload an image of type PNG, JPG or GIF.';
$string['deleteimage'] = 'Delete image';
$string['deleteimage_help'] = "Delete the image for the section being edited.  If you've uploaded an image then it will not replace the deleted image.";
$string['gfreset'] = 'Grid reset options';
$string['gfreset_help'] = 'Reset to Grid defaults.';
$string['defaultimagecontainerwidth'] = 'Default width of the image container';
$string['defaultimagecontainerwidth_desc'] = 'The default width of the image container.';
$string['defaultimagecontainerratio'] = 'Default ratio of the image container relative to the width';
$string['defaultimagecontainerratio_desc'] = 'The default ratio of the image container relative to the width.';
$string['defaultimageresizemethod'] = 'Default image resize method';
$string['defaultimageresizemethod_desc'] = 'The default method of resizing the image to fit the container.';
$string['defaultbordercolour'] = 'Default image container border colour';
$string['defaultbordercolour_desc'] = 'The default image container border colour.';
$string['defaultborderradius'] = 'Default border radius';
$string['defaultborderradius_desc'] = 'The default border radius on / off.';
$string['defaultborderwidth'] = 'Default border width';
$string['defaultborderwidth_desc'] = 'The default border width.';
$string['defaultimagecontainerbackgroundcolour'] = 'Default image container background colour';
$string['defaultimagecontainerbackgroundcolour_desc'] = 'The default image container background colour.';
$string['defaultcurrentselectedsectioncolour'] = 'Default current selected section colour';
$string['defaultcurrentselectedsectioncolour_desc'] = 'The default current selected section colour.';
$string['defaultcurrentselectedimagecontainercolour'] = 'Default current selected image container colour';
$string['defaultcurrentselectedimagecontainercolour_desc'] = 'The default current selected image container colour.';

$string['defaultcoursedisplay'] = 'Course display default';
$string['defaultcoursedisplay_desc'] = "Either show all the sections on a single page or section zero and the chosen section on page.";

$string['defaultfitsectioncontainertowindow'] = 'Fit section container to window by default';
$string['defaultfitsectioncontainertowindow_desc'] = 'The default setting for \'Fit section container to window\'.';

$string['defaultnewactivity'] = 'Show new activity notification image default';
$string['defaultnewactivity_desc'] = "Show the new activity notification image when a new activity or resource are added to a section default.";

$string['setimagecontainerwidth'] = 'Set the image container width';
$string['setimagecontainerwidth_help'] = 'Set the image container width to one of: 128, 192, 210, 256, 320, 384, 448, 512, 576, 640, 704 or 768';
$string['setimagecontainerratio'] = 'Set the image container ratio relative to the width';
$string['setimagecontainerratio_help'] = 'Set the image container ratio to one of: 3-2, 3-1, 3-3, 2-3, 1-3, 4-3 or 3-4.';
$string['setimageresizemethod'] = 'Set the image resize method';
$string['setimageresizemethod_help'] = "Set the image resize method to: 'Scale' or 'Crop' when resizing the image to fit the container.";
$string['setbordercolour'] = 'Set the border colour';
$string['setbordercolour_help'] = 'Set the border colour in hexidecimal RGB.';
$string['setborderradius'] = 'Set the border radius on / off';
$string['setborderradius_help'] = 'Set the border radius on or off.';
$string['setborderwidth'] = 'Set the border width';
$string['setborderwidth_help'] = 'Set the border width between 1 and 10.';
$string['setimagecontainerbackgroundcolour'] = 'Set the image container background colour';
$string['setimagecontainerbackgroundcolour_help'] = 'Set the image container background colour in hexidecimal RGB.';
$string['setcurrentselectedsectioncolour'] = 'Set the current selected section colour';
$string['setcurrentselectedsectioncolour_help'] = 'Set the current selected section colour in hexidecimal RGB.';
$string['setcurrentselectedimagecontainercolour'] = 'Set the current selected image container colour';
$string['setcurrentselectedimagecontainercolour_help'] = 'Set the current selected image container colour in hexidecimal RGB.';

$string['setnewactivity'] = 'Show new activity notification image';
$string['setnewactivity_help'] = "Show the new activity notification image when a new activity or resource are added to a section.";

$string['setfitsectioncontainertowindow'] = 'Fit the section popup to the window';
$string['setfitsectioncontainertowindow_help'] = 'If enabled, the popup box with the contents of the section will fit to the size of the window and will scroll inside if necessary.  If disabled, the entire page will scroll instead.';

$string['colourrule'] = "Please enter a valid RGB colour, six hexadecimal digits.";

// Reset.
$string['resetgrp'] = 'Reset:';
$string['resetallgrp'] = 'Reset all:';
$string['resetimagecontainersize'] = 'Image container size';
$string['resetimagecontainersize_help'] = 'Resets the image container size to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallimagecontainersize'] = 'Image container sizes';
$string['resetallimagecontainersize_help'] = 'Resets the image container sizes to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetimageresizemethod'] = 'Image resize method';
$string['resetimageresizemethod_help'] = 'Resets the image resize method to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallimageresizemethod'] = 'Image resize methods';
$string['resetallimageresizemethod_help'] = 'Resets the image resize methods to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetimagecontainerstyle'] = 'Image container style';
$string['resetimagecontainerstyle_help'] = 'Resets the image container style to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallimagecontainerstyle'] = 'Image container styles';
$string['resetallimagecontainerstyle_help'] = 'Resets the image container styles to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetnewactivity'] = 'New activity';
$string['resetnewactivity_help'] = 'Resets the new activity notification image to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallnewactivity'] = 'New activities';
$string['resetallnewactivity_help'] = 'Resets the new activity notification images to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';
$string['resetfitpopup'] = 'Fit section popup to the window';
$string['resetfitpopup_help'] = 'Resets the \'Fit section popup to the window\' to the default value so it will be the same as a course the first time it is in the Grid format.';
$string['resetallfitpopup'] = 'Fit section popups to the window';
$string['resetallfitpopup_help'] = 'Resets the \'Fit section popup to the window\' to the default value for all courses so it will be the same as a course the first time it is in the Grid format.';

// Capabilities.
$string['grid:changeimagecontainersize'] = 'Change or reset the image container size';
$string['grid:changeimageresizemethod'] = 'Change or reset the image resize method';
$string['grid:changeimagecontainerstyle'] = 'Change or reset the image container style';
