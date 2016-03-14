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
function left_menu(g) {
    // gismo instance
    this.gismo = g;
    
    // current visible list
    this.visible_list;
    
    this.resources_list_names= {"book":"<?php print_string('modulename','book'); ?>","page":"<?php print_string('modulename','page'); ?>","folder":"<?php print_string('modulename','folder'); ?>","resource":"<?php print_string('modulename','resource'); ?>","url":"<?php print_string('modulename','url'); ?>","imscp":"<?php print_string('modulename','imscp'); ?>"};
     
    // lists
    // this field is a javascript object that provides information for the supported lists of items such as icon & tooltip
    this.lists = {
        'users': {
            img: 'users.png', 
            tooltip: '<?php print_string('users', 'block_gismo'); ?>'
        },
        'teachers': {
            img: 'teachers.png', 
            tooltip: '<?php print_string('teachers', 'block_gismo'); ?>'
        },
        'resources': {
            img: 'resources.png', 
            tooltip: '<?php print_string('resources', 'block_gismo'); ?>'
        }, 
        'assignments': {
            img: 'assignments.png', 
            tooltip: '<?php print_string('assignments', 'block_gismo'); ?>'
        },
        'assignments22': {
            img: 'assignments22.png', 
            tooltip: '<?php print_string('assignments22', 'block_gismo'); ?>'
        },
        'chats': {
            img: 'chat.gif', 
            tooltip: '<?php print_string('chats', 'block_gismo'); ?>'
        }, 
        'forums': {
            img: 'forum.gif', 
            tooltip: '<?php print_string('forums', 'block_gismo'); ?>'
        }, 
        'quizzes': {
            img: 'quizzes.png', 
            tooltip: '<?php print_string('quizzes', 'block_gismo'); ?>'
        }, 
        'wikis': {
            img: 'wiki.gif', 
            tooltip: '<?php print_string('wikis', 'block_gismo'); ?>'
        }
    };
    
    // list hidden on load
    // this field specify the lists that have to be hidden on load (icons in the header)
    this.lists_load_hidden = {
        'student': new Array('users', 'teachers'),
        'teacher': new Array()
    };
    
    // list visible on load
    // this field specify the list that has to be shown on load (list body)
    this.lists_load_default = {
        'student': 'resources',
        'teacher': 'users'
    };
    
    // list options
    // this field is a javascript object that provides information about lists for specific analysis
    this.list_options ={
        // students
        'teacher@student-resources-access': {
            'lists': ['users', 'resources'],
            'default': 0,
            'details': ['users']
        },
        'teacher@student-resources-access:users-details': {
            'lists': ['resources'],
            'default': 0,
            'details': []
        }, 
        'teacher@student-accesses': {
            'lists': ['users'],
            'default': 0,
            'details': []
        },
        'teacher@student-accesses-overview': {
            'lists': ['users'],
            'default': 0,
            'details': []
        },
        // resources
        'student@resources-students-overview': {
            'lists': ['resources'],
            'default': 0,
            'details': []
        }, 
        'teacher@resources-students-overview': {
            'lists': ['users', 'resources'],
            'default': 1,
            'details': []
        },  
        'teacher@resources-access': {
            'lists': ['users', 'resources'],
            'default': 1,
            'details': ['resources']
        },
        'student@resources-access': {
            'lists': ['resources'],
            'default': 0,
            'details': []
        },
        'teacher@resources-access:resources-details': {
            'lists': ['users'],
            'default': 0,
            'details': []
        }, 
        // activities -> assignments
        'teacher@assignments': {
            'lists': ['users', 'assignments'],
            'default': 0,
            'details': []
        },
        'student@assignments': {
            'lists': ['assignments'],
            'default': 0,
            'details': []
        },
        // activities -> assignments22
        'teacher@assignments22': {
            'lists': ['users', 'assignments22'],
            'default': 0,
            'details': []
        },
        'student@assignments22': {
            'lists': ['assignments22'],
            'default': 0,
            'details': []
        },
        // activities -> chats
        'teacher@chats': {
            'lists': ['users', 'chats'],
            'default': 0,
            'details': ['users']
        },
        'teacher@chats-over-time': {
            'lists': ['users', 'chats'],
            'default': 0,
            'details': []
        },
        'teacher@chats:users-details': {
            'lists': ['chats'],
            'default': 0,
            'details': []
        },
        'student@chats': {
            'lists': ['chats'],
            'default': 0,
            'details': []
        },
        'student@chats-over-time': {
            'lists': ['chats'],
            'default': 0,
            'details': []
        },
        // activities -> forums
        'teacher@forums': {
            'lists': ['users', 'forums'],
            'default': 0,
            'details': ['users']
        },
        'teacher@forums-over-time': {
            'lists': ['users', 'forums'],
            'default': 0,
            'details': []
        },
        'teacher@forums:users-details': {
            'lists': ['forums'],
            'default': 0,
            'details': []
        },
        'student@forums': {
            'lists': ['forums'],
            'default': 0,
            'details': []
        },
        'student@forums-over-time': {
            'lists': ['forums'],
            'default': 0,
            'details': []
        },
        // activities -> quizzes
        'teacher@quizzes': {
            'lists': ['users', 'quizzes'],
            'default': 0,
            'details': []
        },
        'student@quizzes': {
            'lists': ['quizzes'],
            'default': 0,
            'details': []
        },
        // activities -> wikis
        'teacher@wikis': {
            'lists': ['users', 'wikis'],
            'default': 0,
            'details': ['users']
        },
        'teacher@wikis-over-time': {
            'lists': ['users', 'wikis'],
            'default': 0,
            'details': []
        },
        'teacher@wikis:users-details': {
            'lists': ['wikis'],
            'default': 0,
            'details': []
        },
        'student@wikis': {
            'lists': ['wikis'],
            'default': 0,
            'details': []
        },
        'student@wikis-over-time': {
            'lists': ['wikis'],
            'default': 0,
            'details': []
        },
        // Completion -> assignments
        'teacher@completion-assignments': {
            'lists': ['users', 'assignments'],
            'default': 0,
            'details': []
        },
        'student@completion-assignments': {
            'lists': ['assignments'],
            'default': 0,
            'details': []
        },
        // Completion -> assignments22
        'teacher@completion-assignments22': {
            'lists': ['users', 'assignments22'],
            'default': 0,
            'details': []
        },
        'student@completion-assignments22': {
            'lists': ['assignments22'],
            'default': 0,
            'details': []
        },
        // Completion -> chats
        'teacher@completion-chats': {
            'lists': ['users', 'chats'],
            'default': 0,
            'details': []
        },
        'student@completion-chats': {
            'lists': ['chats'],
            'default': 0,
            'details': []
        },
        // Completion -> forums
        'teacher@completion-forums': {
            'lists': ['users', 'forums'],
            'default': 0,
            'details': []
        },
        'student@completion-forums': {
            'lists': ['forums'],
            'default': 0,
            'details': []
        },        
        // Completion -> quizzes
        'teacher@completion-quizzes': {
            'lists': ['users', 'quizzes'],
            'default': 0,
            'details': []
        },
        'student@completion-quizzes': {
            'lists': ['quizzes'],
            'default': 0,
            'details': []
        },
        // Completion -> resources
        'teacher@completion-resources': {
            'lists': ['users', 'resources'],
            'default': 0,
            'details': []
        },
        'student@completion-resources': {
            'lists': ['resources'],
            'default': 0,
            'details': []
        },
        // Completion -> wikis
        'teacher@completion-wikis': {
            'lists': ['users', 'wikis'],
            'default': 0,
            'details': []
        },
        'student@completion-wikis': {
            'lists': ['wikis'],
            'default': 0,
            'details': []
        }           
    };
    
    // lists methods
    this.get_lists = function() {
        var result = new Array();
        if (this.gismo.util.get_assoc_array_length(this.lists) > 0) {
            for (var k in this.lists) {
                result.push(k);
            }
        }
        return result;
    };
    this.get_lists_by_current_analysis = function () {
        var full_type = this.gismo.get_full_type();
        var result = new Array();
        if (this.list_options[full_type] != undefined) {
            result = this.list_options[full_type]['lists'];
        }
        return result;
    };
    this.get_list_default = function () {
        var full_type = this.gismo.get_full_type();
        var result = 0;
        if (this.list_options[full_type] != undefined) {
            var available_lists = this.get_lists_by_current_analysis();
            if ($.isArray(available_lists) && available_lists[this.list_options[full_type]["default"]] != undefined) {
                result = available_lists[this.list_options[full_type]["default"]];
            }
        }
        return result;
    };
    this.get_list_details = function () {
        var full_type = this.gismo.get_full_type();
        var result = new Array();
        if (this.list_options[full_type] != undefined) {
            result = this.list_options[full_type]['details'];
        }
        return result;
    };
    
    // init lm header method
    this.init_lm_header = function() {
        // local variables
        var item, lm = this;
        // build header
        for (item in this.lists) {
            // add only if not empty
            if (this.gismo.static_data[item].length > 0) {
                $('#' + this.gismo.lm_header_id).append(
                    $('<a></a>')
                        .addClass("list_selector")
                        .attr({"href": "javascript:void(0);", "id": item + "_menu"})
                        .click(
                            {list: item, lm: this},
                            function (event) {
                                event.data.lm.show_list(event.data.list);
                                $(this).blur();
                            }
                        )
                        .append(
                            $('<img></img>')
                                .attr({
                                    "src": "images/" + this.lists[item]["img"],
                                    "alt": "<?php print_string('show', 'block_gismo'); ?> " + this.lists[item]["tooltip"] + " <?php print_string('list', 'block_gismo'); ?>",
                                    "title": "<?php print_string('show', 'block_gismo'); ?> " + this.lists[item]["tooltip"] + " <?php print_string('list', 'block_gismo'); ?>" 
                                })
                        )
                        .css(
                            "display", 
                            (this.lists_load_hidden[this.gismo.actor] != undefined && 
                            $.isArray(this.lists_load_hidden[this.gismo.actor]) && 
                            $.inArray(item, this.lists_load_hidden[this.gismo.actor]) != -1) ? "none" : "inline"
                        )
                );
            }
        }
    };
    
    // unique identifier
    // this function return an identifier for the item
    this.get_unique_id = function(item_type, item, id_field, type_field) {
        var result = false;
        if (id_field != undefined && item[id_field] != undefined) {
            result = (type_field != undefined && item[type_field] != undefined) ? item[type_field] : item_type;
            result += "-" + item[id_field];
        }
        return result;        
    }
    
    // init lm content method
    this.init_lm_content = function() {
        // local variables
        var element, cb_item, cb_label, item;
        var lm = this;
        var count;
        // create lists
        for (item in this.lists) {
            count = this.gismo.get_items_number(item);
            // list
            element = $('<div></div>').attr('id', this.get_list_container_id(item));
            if (count > 0) {
		var lab = "<?php print_string('students', 'block_gismo'); ?>";		// WORKAROUND
		switch (item) {
			case 'users':
				lab = "<?php print_string('students', 'block_gismo'); ?>";
                                break;
			case 'teachers': 
				lab = "<?php print_string('teachers', 'block_gismo'); ?>";  
                                break;  
			case 'resources':
				lab = "<?php print_string('resources', 'block_gismo'); ?>";
                                break;
                        case 'assignments':
				lab = "<?php print_string('assignments', 'block_gismo'); ?>";
                                break;
			case 'assignments22':
				lab = "<?php print_string('assignments22', 'block_gismo'); ?>";
                                break;
			case 'forums':
				lab = "<?php print_string('forums', 'block_gismo'); ?>";
                                break;
			case 'wikis':
				lab = "<?php print_string('wikis', 'block_gismo'); ?>";
                                break;
                        case 'chats':
				lab = "<?php print_string('chats', 'block_gismo'); ?>";
                                break;
                        case 'quizzes':
				lab = "<?php print_string('quizzes', 'block_gismo'); ?>";
                                break;
			default:
				lab = "<?php print_string('items', 'block_gismo'); ?>";
		}
                
                // add header with a checkbox to control items selection
                element.append(
                    $('<div></div>').addClass("cb_main").append(
                        $("<label></label>")
                            .addClass("cb_label")
                            .html("<b>" + lab.toUpperCase() + " (" + count + " <?php print_string('items', 'block_gismo'); ?>)</b>")
                            .prepend(
                                $('<input></input>').addClass("cb_element")
                                    .attr({
                                        "type": "checkbox",
                                        "value": "0",
                                        "name": item + "_cb_control",
                                        "id": item + "_cb_control"
                                    })
                                    .prop("checked", true)
                                    .click(
                                        {list: item},
                                        function(event) {
                                            $('#' + event.data.list + '_list input:checkbox').prop('checked', $(this).prop('checked'));
                                            if (lm.gismo.current_analysis.plot != null && 
                                                lm.gismo.current_analysis.plot != undefined) {
                                                lm.gismo.update_chart();
                                            }
                                        }
                                    )
                            )
                    )
                );
                var oldtype; //Used for resources type
                var typename; //Name of resources type (language difference)
                
                // add items checkboxes
                for (var k=0; k<this.gismo.static_data[item].length; k++) {                
                    if (this.gismo.is_item_visible(this.gismo.static_data[item][k])) {
                        if(this.gismo.static_data[item][k]['type']!==undefined){ //ONLY RESOURCES HAVE TYPE ATTRIBUTE
                             
                            if(oldtype == undefined || oldtype != this.gismo.static_data[item][k]['type']){ //Check if new type then we must print the TYPE instead of the element
                                oldtype = this.gismo.static_data[item][k]['type'];
                                typename=this.resources_list_names[oldtype];
                                cb_item = $('<input></input>').attr("type", "checkbox");
                                cb_item.attr("value", oldtype);                         
                                cb_item.attr("name", oldtype);
                                cb_item.attr("id", oldtype);
                                cb_item.prop("checked", true);
                                cb_item.addClass("cb_element");
                                cb_item.bind("click", {}, function (event) {
                                    item_value= $(this).prop('checked');//get attribute checked -> true or false
                                    
                                    $("input:checkbox[value^="+this.name+"-]").each(function(element){  //get all elements with this type
                                        $(this).prop('checked', item_value);  //Set attribute checked -> true or false
                                    });
                                    
                                    // manage global checkbox
                                    var selector = '#resources_list input[id!=resources_cb_control]:checkbox'; //get all checkboxes in resources_list except of resources_cb_control
                                    var global_checked = ($(selector).length === $(selector + ":checked").length) ? true : false;
                                    $('input#resources_cb_control').prop('checked', global_checked);
                                    
                                    // update chart
                                    if (lm.gismo.current_analysis.plot != null && lm.gismo.current_analysis.plot != undefined) {
                                        lm.gismo.update_chart();
                                    } 
                                    
                                }); 
                                cb_label = $("<label style='float: left;'></label>")
                                        .html(typename);
                                cb_label.addClass("cb_label_type");
                                cb_label.prepend(cb_item);
                                element.append(
                                    $("<div></div>").addClass("cb")
                                    .append(cb_label)                                    
                                );
                            }
                        }   
                        cb_item = $('<input></input>').attr("type", "checkbox");
                        // cb_item.attr("value", this.gismo.static_data[item][k].id);
                        cb_item.attr("value", this.get_unique_id(item, this.gismo.static_data[item][k], "id", "type"));                         
                        cb_item.attr("name", item + "_cb[" + this.gismo.static_data[item][k].id + "]");
                        cb_item.attr("id", item + "_cb_" + this.gismo.static_data[item][k].id);
                        cb_item.prop("checked", true);
                        cb_item.addClass("cb_element");
                        cb_item.bind("click", {list: item}, function (event) {
                        
                            if(this.value.split("-")[0]!==undefined){ //ONLY RESOURCES HAVE TYPE ATTRIBUTE - update type checkbox value
                                 // manage type checkbox
                                 var selector = '#'+event.data.list+'_list input[value^='+this.value.split("-")[0]+'-]:checkbox'; //get all checkboxes in the type list
                                 var global_checked = ($(selector).length === $(selector + ":checked").length) ? true : false;
                                 $('input#' + this.value.split("-")[0]).prop('checked', global_checked);
                            }
                            
                            // if alt key has been pressed then this is the only one selected
                            if (event.altKey) {
                                $('#' + event.data.list + '_list input:checkbox').prop('checked', false);
                                $(this).prop('checked', true);
                            }
                            // manage global cb
                            var selector = '#' + event.data.list + '_list input[id!=' + event.data.list + '_cb_control]:checkbox';
                            var global_checked = ($(selector).length === $(selector + ":checked").length) ? true : false;
                            $('input#' + event.data.list + '_cb_control').prop('checked', global_checked);     
                            
                            // update chart
                            if (lm.gismo.current_analysis.plot != null && lm.gismo.current_analysis.plot != undefined) {
                                lm.gismo.update_chart();
                            }
                        });                        
                        cb_label = $("<label style='float: left;'></label>")
                                        .html(this.gismo.static_data[item][k].name)
                                        .mouseover(function () {
                                            $(this).addClass("selected");
                                        })
                                        .mouseout(function () {
                                            $(this).removeClass("selected");
                                        });
                        cb_label.addClass("cb_label");
                        cb_label.prepend(cb_item);
                        element.append(
                            $("<div></div>").addClass("cb")
                            .append(cb_label)
                            .append(
                                $("<image style='float: left; margin-top: 3px; margin-left: 5px;'></image>")
                                .attr("id", item + "_" + this.gismo.static_data[item][k].id)
                                .attr("restype", oldtype)
                                .attr({src: "images/eye.png", title: "<?php print_string('details', 'block_gismo'); ?>"})
                                .addClass(item + "_details image_link float_right")
                                .mouseover(function () {
                                    $(this).parent().addClass("selected");
                                })
                                .mouseout(function () {
                                    $(this).parent().removeClass("selected");
                                })
                                .click(function () {
                                    var options = $(this).attr("id").split("_");
                                    if(g.current_analysis.type == "resources-access"){
                                        g.analyse(g.current_analysis.type, {subtype: options[0] + "-details", id: options[1], restype: $(this).attr("restype")});
                                    }else{
                                        g.analyse(g.current_analysis.type, {subtype: options[0] + "-details", id: options[1]});
                                    }
                                })
                            )
                        );
                    }
                }
            } else {
                element.html("<p>There isn't any " + item + " in the course!</p>");
            }
            element.hide();
            $('#' + this.gismo.lm_content_id).append(element);
        }
        $('#' + this.gismo.lm_content_id).append($('<br style="clear: both;" />'));
        $('#' + this.gismo.lm_content_id).append($('<div></div>').css({"height": "10px"}))  
    };
    
    this.init_lm_content_details = function() {
        // hide all details controls
        var selectors = new Array(), lists = this.get_lists(), k;
        for (k=0;k<lists.length;k++) {
            selectors.push("." + lists[k] + "_details");
        }
        $(selectors.join(", ")).hide();
        // show detais for current analysis
        var details = this.get_list_details();
        for (k in details) {
            $("." + details[k] + "_details").show();
        }
    };

    // clean
    this.clean = function () {
        // clean header
        $('#' + this.gismo.lm_header_id + " .list_selector").remove();
        // clean content
        $('#' + this.gismo.lm_content_id).empty();
    };
    
    // init method
    this.init = function () {
        // clean
        this.clean();
        // set default visible list
        this.visible_list = "resources";
        if (this.lists_load_default[this.gismo.actor] != undefined &&
            $.inArray(this.lists_load_default[this.gismo.actor], this.get_lists()) != -1) {
            this.visible_list = this.lists_load_default[this.gismo.actor];
        }
        // init header (link icons)
        this.init_lm_header();
        // init content (build lists)
        this.init_lm_content();
        // show / hide items details
        this.init_lm_content_details();
        // show current list
        this.show_list(this.visible_list);
    };
    
    this.get_list_container_id = function (list) {
        return list + "_list";    
    };
    
    this.show_list = function (list) {
        // hide previous list
        $("#" + this.get_list_container_id(this.visible_list)).hide();
        // show new list
        $("#" + this.get_list_container_id(list)).show();
        // update current list
        this.visible_list = list;
    };
    
    this.get_selected_items = function () {
        var selected_items = new Array();
        for (var item in this.lists) {
            selected_items[item] = new Array();
            $("#" + this.get_list_container_id(item) + " input:checkbox:checked").each(function (index) {
                selected_items[item].push($(this).val());            
            });    
        }
        return selected_items;            
    };
   
    this.set_menu = function (fresh) {
        // all available lists
        var all = this.get_lists();
        // enabled lists (according to current analysis)
        var enabled = this.get_lists_by_current_analysis();
        // visible list (according to current analysis)
        var visible = this.get_list_default();
        // keep visible list ?
        if (fresh == false && $.inArray(this.visible_list, enabled) !== -1) {
            visible = this.visible_list;
        }
        // set lists visibility (icons in the header)
        for (var item in all) {
            if ($.inArray(all[item], enabled) !== -1) {
                $("#" + all[item] + "_menu").show();     
            } else {
                $("#" + all[item] + "_menu").hide();
            }
        }
        // show correct list (list content)
        this.show_list(visible);
    };
    
    this.show = function() {
        $('#open_control').hide(); 
        $('#close_control').show(); 
        $('#left_menu').show();
        $('#left_menu').toggleClass('closed_lm'); 
        $('#chart').toggleClass('expanded_ch');
        if (this.gismo.get_full_type() != null) {
            this.gismo.update_chart();   
        }   
    };
    
    this.hide = function() {
        $('#open_control').show(); 
        $('#close_control').hide(); 
        $('#left_menu').hide();
        $('#chart').toggleClass('expanded_ch');
        $('#left_menu').toggleClass('closed_lm'); 
        if (this.gismo.get_full_type() != null) {
            this.gismo.update_chart();   
        }
    };

    // info
    this.show_info = function() {
        var title = "<?php print_string('info_title', 'block_gismo'); ?>";	    
        var message = "<?php print_string('info_text', 'block_gismo'); ?>";
        this.gismo.util.show_modal_dialog(title, message);
    };
}
