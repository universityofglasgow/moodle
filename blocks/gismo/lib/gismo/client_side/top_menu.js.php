<?PHP

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//Fix from Corbière Alain - http://sourceforge.net/p/gismo/wiki/Home/#cf25
header("Content-type: application/javascript ; charset=UTF-8") ;

// define constants
define('ROOT', (realpath(dirname( __FILE__ )) . DIRECTORY_SEPARATOR));
define('LIB_DIR', ROOT . "lib" . DIRECTORY_SEPARATOR);    

// include moodle config file
require_once realpath(ROOT . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config.php");
$PAGE->set_context(context_system::instance()); //Tim Lock Fix up some page context warnings 

?>
function top_menu(g) {
    // gismo instance
    this.gismo = g;
    
    // ui components
    this.menu_id = "menu";
    
    // lists check object that specify the status for each list (true if not empty, false otherwise)
    this.lists_status = {};
    
    // menu definition
    this.menu = new Array(
        // File menu
        {
            "label": "<?php print_string('file', 'block_gismo'); ?>",
            "action": null,
            "roles": new Array("teacher", "student"),
            "require": null,
            "sub": new Array(
                { 
                    "label": "<?php print_string('options', 'block_gismo'); ?>", 
                    "action": "g.options()", 
                    "roles": new Array("teacher"), 
                    "require": null, 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('save', 'block_gismo'); ?>", 
                    "action": "g.save_as_image()", 
                    "roles": new Array("teacher", "student"), 
                    "require": null, 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('exit', 'block_gismo'); ?>", 
                    "action": "g.exit()", 
                    "roles": new Array("teacher", "student"), 
                    "require": null, 
                    "sub": null 
                }
            )
        },
        // Students menu
        {
            "label": "<?php print_string('students', 'block_gismo'); ?>",
            "action": null,
            "roles": new Array("teacher"),
            "require": null,
            "sub": new Array(
                { 
                    "label": "<?php print_string('student_accesses', 'block_gismo'); ?>", 
                    "action": "g.analyse('student-accesses')", 
                    "roles": new Array("teacher"), 
                    "require": null, 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('student_accesses_overview', 'block_gismo'); ?>", 
                    "action": "g.analyse('student-accesses-overview')", 
                    "roles": new Array("teacher"), 
                    "require": null, 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('student_resources_overview', 'block_gismo'); ?>", 
                    "action": "g.analyse('student-resources-access')", 
                    "roles": new Array("teacher"), 
                    "require": null, 
                    "sub": null 
                }
            )
        },
        // Resources menu
        {
            "label": "<?php print_string('resources', 'block_gismo'); ?>",
            "action": null,
            "roles": new Array("teacher", "student"),
            "require": null,
            "sub": new Array(
                { 
                    "label": "<?php print_string('resources_students_overview', 'block_gismo'); ?>", 
                    "action": "g.analyse('resources-students-overview')", 
                    "roles": new Array("teacher", "student"), 
                    "require": null, 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('resources_access_overview', 'block_gismo'); ?>", 
                    "action": "g.analyse('resources-access')", 
                    "roles": new Array("teacher", "student"), 
                    "require": null, 
                    "sub": null 
                }
            )
        },
        // Activities menu
        {
            "label": "<?php print_string('activities', 'block_gismo'); ?>",
            "action": null,
            "roles": new Array("teacher", "student"),
            "require": null,
            "sub": new Array(
                { 
                    "label": "<?php print_string('assignments', 'block_gismo'); ?>", 
                    "action": "g.analyse('assignments')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("assignments"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('assignments22', 'block_gismo'); ?>", 
                    "action": "g.analyse('assignments22')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("assignments22"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('chats', 'block_gismo'); ?>", 
                    "action": "g.analyse('chats')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("chats"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('chats_over_time', 'block_gismo'); ?>", 
                    "action": "g.analyse('chats-over-time')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("chats"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('forums', 'block_gismo'); ?>", 
                    "action": "g.analyse('forums')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("forums"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('forums_over_time', 'block_gismo'); ?>", 
                    "action": "g.analyse('forums-over-time')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("forums"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('quizzes', 'block_gismo'); ?>", 
                    "action": "g.analyse('quizzes')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("quizzes"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('wikis', 'block_gismo'); ?>", 
                    "action": "g.analyse('wikis')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("wikis"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('wikis_over_time', 'block_gismo'); ?>", 
                    "action": "g.analyse('wikis-over-time')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("wikis"), 
                    "sub": null 
                }
            )
        },
        // Completion menu
        {
            "label": "<?php print_string('completion', 'block_gismo'); ?>",
            "action": null,
            "roles": new Array("teacher", "student"),
            "require": "COMPLETION_ENABLED", //Require completion enabled on SITE & COURSE
            "sub": new Array(   
                { 
                    "label": "<?php print_string('completion_assignment_menu', 'block_gismo'); ?>", 
                    "action": "g.analyse('completion-assignments')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("assignments"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('completion_assignment22_menu', 'block_gismo'); ?>", 
                    "action": "g.analyse('completion-assignments22')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("assignments22"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('completion_chat_menu', 'block_gismo'); ?>", 
                    "action": "g.analyse('completion-chats')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("chats"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('completion_forum_menu', 'block_gismo'); ?>", 
                    "action": "g.analyse('completion-forums')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("forums"), 
                    "sub": null 
                },             
                { 
                    "label": "<?php print_string('completion_quiz_menu', 'block_gismo'); ?>", 
                    "action": "g.analyse('completion-quizzes')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("quizzes"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('completion_resource_menu', 'block_gismo'); ?>", 
                    "action": "g.analyse('completion-resources')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("resources"), 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('completion_wiki_menu', 'block_gismo'); ?>", 
                    "action": "g.analyse('completion-wikis')", 
                    "roles": new Array("teacher", "student"), 
                    "require": new Array("wikis"), 
                    "sub": null 
                }
            )
        },        
        // Help menu
        {
            "label": "<?php print_string('help', 'block_gismo'); ?>",
            "action": null,
            "roles": new Array("teacher", "student"),
            "require": null,
            "sub": new Array(
                { 
                    "label": "<?php print_string('help_docs', 'block_gismo'); ?>", 
                    "action": "g.show_help()", 
                    "roles": new Array("teacher", "student"), 
                    "require": null, 
                    "sub": null 
                },
                { 
                    "label": "<?php print_string('tutorial', 'block_gismo'); ?>", 
                    "action": "g.show_short_overview()", 
                    "roles": new Array("teacher", "student"), 
                    "require": null, 
                    "sub": null 
                },
		{
                    "label": "<?php print_string('about', 'block_gismo'); ?>", 
                    "action": "g.about()", 
                    "roles": new Array("teacher", "student"), 
                    "require": null, 
                    "sub": null 
                }
            )
        }
    );
    
    // init menu
    // this method initialize the menu (structure and animation)
    this.init = function () {
        // check lists
        this.check_lists();
        
        // build menu structure
        this.build($("#" + this.menu_id), this.menu, true);
        
        // animate menu
        this.animate_menu();
    }
    
    // check lists
    this.check_lists = function () {
        var lists = this.gismo.lm.get_lists();
        if ($.isArray(lists) && lists.length > 0) {
            for (var k=0; k<lists.length; k++) {
                this.lists_status[lists[k]] = (this.gismo.static_data[lists[k]].length > 0) ? true : false;
            }
        }
    }
    
    // build menu
    // this method builds the menu structure working on its definition (menu field)
    this.build = function (container, items, first_level) {
        var k, i, check, el, tmp, list = $("<ul></ul>");
        // id for the first level ul
        if (first_level) {
            list.attr("id", "panelMenu");
        }
        for (k in items) {
            // check on role
            check = ($.isArray(items[k].roles) && $.inArray(g.actor, items[k].roles) != -1);
            // check on requirements
            if ($.isArray(items[k].require) && items[k].require.length > 0) {
                for (i=0; i<items[k].require.length; i++) {
                    check = check && this.lists_status[items[k].require[i]];
                }
            }else if(items[k].require=="COMPLETION_ENABLED"){ //Require completion on SITE & COURSE
                check = check && g.completionenabled;
            }
            // build item and sub items only if check is true
            if (check) {
                // add entry
                el = $("<li></li>");
                tmp = (first_level) ? items[k].label + '&nbsp;&nbsp;<img src="images/menu_icon.gif" alt="">' : $("<div></div>").append($("<nobr></nobr>").html(items[k].label));
                list.append(
                    el.append(
                        $("<a></a>")
                            .attr({
                                "href": (items[k].action != null) ? "javascript:" + items[k].action : "javascript:void(0)"
                            })
                            .append(tmp)
                    )
                );
                // sub (go in depth)
                if ($.isArray(items[k].sub) && items[k].sub.length > 0) {
                    this.build(el, items[k].sub, false);
                }
            }
        }
        // append
        container.append(list);
    }
    
    // animate menu
    // this method animates the menu
    this.animate_menu = function () {
        var timeout    = 500;
        var menu_timer = 0;
        var ddmenuitem = 0;

        function menu_open() {
            jsddm_canceltimer();
            menu_close();
            ddmenuitem = $(this).find('ul').css('visibility', 'visible');
            $(this).children('a').addClass('menu_open');
            $(this).children('a').children('img').attr('src', 'images/menu_icon_selected.gif');
        }

        function menu_close(a) {  
            if (ddmenuitem) {
                ddmenuitem.css('visibility', 'hidden');    
            }
            $('#panelMenu > li').children('a').removeClass('menu_open');
            $('#panelMenu > li').children('a').children('img').attr('src', 'images/menu_icon.gif');
        }

        function menu_close_scheduler() {
            menu_timer = window.setTimeout(menu_close, timeout);
        }

        function jsddm_canceltimer() {  
            if (menu_timer) {
                window.clearTimeout(menu_timer);
                menu_timer = null;
            }
        }

        $(document).ready(function() {  
            $('#panelMenu > li').bind('mouseover', menu_open);
            $('#panelMenu > li').bind('mouseout',  menu_close_scheduler);
        });

        document.onclick = menu_close;    
    }
}
