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
?>
<div id="app_content">
    <div id="left_menu">
        <div id="lm_header" class="ct_header">
            <!-- Users / Resources / Assignments / Quizzes menu -->
            <img class="image_link" id="close_control" src="images/close.png" alt="<?php print_string('hide_menu', 'block_gismo'); ?>" title="<?php print_string('hide_menu', 'block_gismo'); ?>" style="float: right; margin: 0; padding: 0;" onclick="javascript:g.lm.hide();" />
            <img class="image_link" id="left_menu_info" src="images/left_menu_info.gif" alt="<?php print_string('show_details', 'block_gismo'); ?>" title="<?php print_string('show_details', 'block_gismo'); ?>" style="float: right; margin-right: 15px;"  onclick="javascript:g.lm.show_info();" />
        </div>
        <div id="lm_content"><!-- Users / Resources / Assignments / Quizzes lists --></div>    
    </div>
    <div id="chart">
        <div id="ch_header" class="ct_header">
            <img class="image_link" id="open_control" src="images/open.png" alt="<?php print_string('show_menu', 'block_gismo'); ?>" title="<?php print_string('show_menu', 'block_gismo'); ?>" style="float: left; margin: 0; padding: 0; margin-right: 5px; display: none;" onclick="javascript:g.lm.show();" />
            <div id="course_name">
            </div>
            <div id="title"><!-- Chart title --></div>
        </div>
        <div id="ch_content">
            <div id="error_message">
                <div id="title"></div>
                <p id="message"></p>
            </div>
            <div id="processing">
                <div id="p_img"><img src="images/processing.gif" alt="<?php print_string('homepage_processing_data', 'block_gismo'); ?>" /></div>
                <p id="p_message"><?php print_string('homepage_processing_data_wait', 'block_gismo'); ?></p>
            </div>
            <div id="plot_container">
                <div id="plot">
                    <!-- Chart -->
                </div>
            </div>
            <div id="welcome_page">
                <h1 align="center"><?php print_string('homepage_title', 'block_gismo'); ?></h1>

                <div style="width:70%;text-align:center;margin:0 auto;font-size:130%;">
                    <p><?php print_string('homepage_text', 'block_gismo'); ?></p>
                </div>
                <div id="slideshowWrapper" style="margin: 0 auto; text-align: center;">
                    <h2><?php print_string('homepage_charts_preview_title', 'block_gismo'); ?></h2>
                    <ul id="slideshow" class="slideshow">                        
                        <li>
                            <img src="images/help/slider_activities_assignments.png" width="400" height="300" border="0" alt="" />
                            <div class="ss_caption"><?php print_string('homepage_chart_activities_assignments_overview', 'block_gismo'); ?></div>
                        </li>
                        <li>
                            <img src="images/help/slider_resources_accesses_overview.png" width="400" height="300" border="0" alt="" />
                            <div class="ss_caption"><?php print_string('homepage_chart_resources_access_overview', 'block_gismo'); ?></div>
                        </li>
                        <li>
                            <img src="images/help/slider_resources_students_overview.png" width="400" height="300" border="0" alt="" />
                            <div class="ss_caption"><?php print_string('homepage_chart_resources_students_overview', 'block_gismo'); ?></div>
                        </li> 
                        <li>
                            <img src="images/help/slider_students_accesses_overview_on_resources.png" width="400" height="300" border="0" alt="" />
                            <div class="ss_caption"><?php print_string('homepage_chart_students_access_overview_on_resources', 'block_gismo'); ?></div>
                        </li>
                        <li>
                            <img src="images/help/slider_students_accesses_overview.png" width="400" height="300" border="0" alt="" />
                            <div class="ss_caption"><?php print_string('homepage_chart_students_access_overview', 'block_gismo'); ?></div>
                        </li>
                        <li>
                            <img src="images/help/slider_students_accesses_by_students.png"width="400" height="300" border="0" alt="" />
                            <div class="ss_caption"><?php print_string('homepage_chart_students_accesses_by_students', 'block_gismo'); ?></div>
                        </li>
                    </ul><br clear="all" />
                </div>
                <script type="text/javascript">
                    $(document).ready(function () {
                        $('#slideshow').fadeSlideShow();
                    });
                </script>
            </div>           
        </div>    
    </div>
</div>
<div id="help" style="display: none;">
    <?php require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "help.php"); ?>
</div>