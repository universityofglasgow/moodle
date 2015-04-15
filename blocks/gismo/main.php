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

// fetch static data
$gismo_static_data = new block_gismo\FetchStaticDataMoodle($course->id, $actor);
$gismo_static_data->init();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo get_string('page_title', 'block_gismo') . " " . $gismo_static_data->fullname; ?></title>
        <!-- client side libraries START -->
        <?php
        if (is_array($client_side_libraries) AND count($client_side_libraries) > 0) {
            foreach ($client_side_libraries as $key => $client_side_libs) {
                if (is_array($client_side_libs) AND count($client_side_libs) > 0) {
                    foreach ($client_side_libs as $client_side_lib) {
                        $res = explode('.', $client_side_lib);
                        $ext = '';
                        if (count($res) < 2) { //no extension inserted in the name included
                            $ext = '.js';
                        }
                        $lib_full_path = LIB_DIR . $key . DIRECTORY_SEPARATOR . "client_side" . DIRECTORY_SEPARATOR . $client_side_lib . $ext;
                        if (is_file($lib_full_path) AND is_readable($lib_full_path)) {
                            ?>
                            <script type="text/javascript" src="lib/<?php echo $key . "/client_side/" . $client_side_lib . $ext; ?>"></script>
                            <?php
                        }
                    }
                }
            }
        }
        ?>
<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="lib/third_parties/client_side/jqplot.1.0.8r1250/excanvas.js"></script><![endif]-->
        <!-- client side libraries END -->
        <link rel="stylesheet" href="style/gismo.css" type="text/css" media="screen" charset="utf-8" />
        <link rel="stylesheet" href="lib/third_parties/client_side/jquery-ui-1.10.3/css/ui-darkness/jquery-ui-1.10.3.custom.min.css" type="text/css" media="screen" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="lib/third_parties/client_side/jqplot.1.0.8r1250/jquery.jqplot.min.css" />
        <link rel="stylesheet" type="text/css" href="lib/third_parties/client_side/simpleFadeSlideShow/style.css" />
        <?php
        // static data + gismo instance not needed by help page 
        if (!in_array($query, array("help"))) {
            ?>
            <script type="text/javascript">
                // <!--

                // static data
                var config = <?php echo $block_gismo_config; ?>;
                var srv_data = '<?php echo $srv_data_encoded; ?>';
                var static_data = new Array();
                static_data['users'] = <?php echo $gismo_static_data->users; ?>;
                static_data['teachers'] = <?php echo $gismo_static_data->teachers; ?>;
                static_data['resources'] = <?php echo $gismo_static_data->resources; ?>;
                static_data['assignments'] = <?php echo $gismo_static_data->assignments; ?>;
                static_data['assignments22'] = <?php echo $gismo_static_data->assignments22; ?>;
                static_data['chats'] = <?php echo $gismo_static_data->chats; ?>;
                static_data['forums'] = <?php echo $gismo_static_data->forums; ?>;
                static_data['quizzes'] = <?php echo $gismo_static_data->quizzes; ?>;
                static_data['wikis'] = <?php echo $gismo_static_data->wikis; ?>;
                static_data['course_full_name'] = '<?php echo str_replace("'", "\\'", $gismo_static_data->fullname); ?>';
                var course_start_time = <?php echo $gismo_static_data->start_time; ?>;
                var current_time = <?php echo $gismo_static_data->end_time; ?>;
                var actor = '<?php echo $actor; ?>';
                var completionenabled = <?php if ($CFG->enablecompletion && $course->enablecompletion) {
            echo "true";
        } else {
            echo "false";
        } ?>; //Added completionenabled  

                // gismo instance
                var g = new gismo(config, srv_data, static_data, course_start_time, current_time, actor, completionenabled); //Added completionenabled

                // initialize application
                $(document).ready(function () {
                    // init
                    g.init();

                    // window resize event
                    $(window).resize(function () {
                        g.resize();
                    });

                    // force resize
                    setTimeout(function () {
                        g.resize();
                    }, 100);
                });

                // -->
            </script>
    <?php
}
?>
    </head>
    <body>
        <div id="dialog"></div>
        <div id="header">
            <form class="hidden" id="save_form" name="save_form" action="export_as_image.php" target="_blank" method="post">
                <textarea class="hidden" id="data" name="data"></textarea>
                <input class="hidden" type="hidden" id="chart_id" name="chart_id" value="" />
            </form>
            <div id="menu">
                <ul id="panelMenu"></ul>
            </div>
            <a id="logo" href="http://gismo.sourceforge.net" target="_blank"><img src="images/logo.png" /></a>
        </div>
        <div id="content">
            <?php
            // content and footer
            switch ($query) {
                case "help":
                    $content = "template" . DIRECTORY_SEPARATOR . "help.php";
                    $footer = false;
                    break;
                default:
                    $content = "template" . DIRECTORY_SEPARATOR . "home.php";
                    $footer = true;
                    break;
            }
            require_once $content;
            ?>
        </div>
<?php
if ($footer) {
    ?>
            <div id="footer">
                <input id="from_date" name="from_date" type="text" class="input_date" />
                <div id="f1">
                    <input id="to_date" name="to_date" type="text" class="input_date" />
                    <div id="date_slider"></div>
                </div>
            </div>
    <?php
}
?>
    </body>
<?php
if (in_array($query, array("help"))) {
    // HACK     
    ?> 
        <script>
            $(document).ready(function () {
                $('#panelMenu > li').bind('mouseover', function () {
                    $(this).children('a').addClass('menu_open');
                });
                $('#panelMenu > li').bind('mouseout', function () {
                    $(this).children('a').removeClass('menu_open');
                });
            });
        </script>
    <?php
}
?>	
</html>
