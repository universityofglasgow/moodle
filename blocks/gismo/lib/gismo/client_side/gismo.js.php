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
function gismo(config, srv_data, static_data, course_start_time, current_time, actor, completionenabled) { //Added completionenabled
    // html elements ids
    // header
    this.header_id = 'header';
    // content
    this.content_id = 'content';
    this.left_menu_id = 'left_menu';
    this.lm_header_id = 'lm_header';
    this.lm_content_id = 'lm_content';
    this.chart_id = 'chart';
    this.plot_container_id = 'plot_container';
    this.plot_id = 'plot';
    this.error_message_id = 'error_message';
    this.processing_id = 'processing';
    this.ch_header_id = 'ch_header';
    this.ch_content_id = 'ch_content';
    // footer
    this.footer_id = 'footer';
    this.date_slider_id = 'date_slider';
    this.from_date_id = 'from_date';
    this.to_date_id = 'to_date';
    
    // fields
    this.actor = actor;
    this.completionenabled = completionenabled; //Completion on SITE & COURSE
    this.srv_data = srv_data;
    this.static_data = static_data;
    this.course_start_time = course_start_time;
    this.current_time = current_time;
    this.current_analysis = {
        type: null,
        options: null,
        name: null,
        data: null,
        extra_info: null,
        prepared_data: null,
        plot: null,
        status: false
    };
    
    // resize management
    this.resize_scheduled = false;
    this.last_resize = 0;
    
    // config
    this.cfg = config;
    
    // util
    this.util = new gismo_util(this);
    
    // composite (instances)
    this.tm = new top_menu(this);
    this.lm = new left_menu(this);
    this.cht = null;
    this.tl = new time_line(this);
    
    // init method
    this.init = function () {
        // show content section
        this.show_content();
        // init top menu
        this.tm.init();
        // init left menu
        this.lm.init();
        // init time line
        this.tl.init();
        // other stuff
        $("#" + this.plot_id).hide();
        $("#" + this.error_message_id).hide();
        $("#" + this.processing_id).hide();
        // set course
        $("#" + this.ch_header_id + " #course_name").html(this.util.intelligent_substring(this.static_data["course_full_name"], true, 100, 5));
    };
    
    // reset methods
    this.reset = function () {
        this.reset_data();
        this.reset_dom();
    }
    this.reset_data = function () {
        // destroy plot
        if (this.current_analysis.plot != null) {
            this.current_analysis.plot.destroy();
        }
    }
    this.reset_dom = function () {
        // destroy plot
        if (this.current_analysis.plot != null) {
            this.current_analysis.plot.destroy();
        }
        // delete chart from DOM
        $("#" + this.plot_id).remove();
        $("#" + this.plot_container_id).empty().append(
            $("<div></div>").attr({"id": this.plot_id})
        );
    }
    
    // this method return type & subtype combined (used to decide how to prepare date / create chart)
    this.get_full_type = function () {
        var full_type = null;
        if (this.current_analysis.type != null) {
            full_type = this.actor + "@" + this.current_analysis.type;
            if (this.current_analysis.options != null && 
                this.current_analysis.options.subtype != undefined && 
                this.current_analysis.options.subtype != null) {
                full_type = full_type + ":" + this.current_analysis.options.subtype;    
            }    
        }
        return full_type;   
    };
    
    this.days_between = function (iso_date1, iso_date2) {   // date yyyy-mm-dd
        // milliseconds in a day
        var day_ms = 1000 * 60 * 60 * 24;
        var date1 = iso_date1.split("-");
        var date2 = iso_date2.split("-");
        
        // Convert both dates to milliseconds
        var date1_ms = (new Date(date1[0], date1[1], date1[2], 0, 0, 0, 0)).getTime();
        var date2_ms = (new Date(date2[0], date2[1], date2[2], 0, 0, 0, 0)).getTime();

        // Calculate the difference in milliseconds
        var difference_ms = Math.abs(date1_ms - date2_ms);
        
        // Convert back to days and return
        return Math.round(difference_ms / day_ms);

    };
    
    this.show_error = function (message, title) {
        var t = (title == undefined) ? "An error has occurred" : title;
        // hide welcome page
        $("#welcome_page").hide();
        // hide current plot
        $("#" + this.plot_id).hide();
        // hide processing
        $("#" + this.processing_id).hide();
        // empty chart
        this.reset();
        // $("#" + this.plot_id).empty();
        // $("#" + this.plot_id).html("");
        // set error message
        $("#" + this.error_message_id + " #title").html(t);
        $("#" + this.error_message_id + " #message").html(message);
        // show message
        $("#" + this.error_message_id).show();
    };
    
    this.show_processing = function () {
        // hide welcome page
        $("#welcome_page").hide();
        // hide current plot
        $("#" + this.error_message_id).hide();
        // empty chart
        this.reset();
        // $("#" + this.plot_id).empty();
        // $("#" + this.plot_id).html("");
        $("#" + this.plot_id).height(0);
        // show processing
        $("#" + this.processing_id).show();
    };
    
    // analyse method
    this.analyse = function (type, options) {
        // variables
        var response;
        
        // options
        var opt = ""
        if (options != undefined) {
            for (var k in options) {
                opt += "&" + k + "=" + options[k];        
            }
        }
        
        // show content section
        this.show_content();
        
        // reset data (variables / existing chart)
        this.reset();
        
        // show processing
        this.show_processing();
        
        // extract data from server
        $.ajax({
            url: 'ajax.php',
            async: false, 
            type: 'POST',
            data: 'q=' + this.actor + "@" + type + opt + '&srv_data=' + this.srv_data + '&from=' + this.tl.get_from(true) + '&to=' + this.tl.get_to(true) + '&token=' + Math.random(), 
            dataType: 'json',
            success: 
                function(json) {
                    response = json;
                },
            error:
                function(error) {
                    response = {error: '1', message: '<?php print_string('err_cannot_extract_data', 'block_gismo'); ?>'};    
                }
        });
        
        // check response for errors
        if (response['error'] != undefined && response['error'] == '1') {
            if (response['message'] != undefined) {
                this.show_error(response['message']);
            } else {
                this.show_error('<?php print_string('err_unknown', 'block_gismo'); ?>');    
            }    
        } else {
            // save data
            this.current_analysis.type = type;
            this.current_analysis.options = (options != undefined) ? options : null;
            this.current_analysis.name = response.name;
            this.current_analysis.links = response.links;
            this.current_analysis.data = response.data;
            this.current_analysis.extra_info = response.extra_info;
            
            // show / hide menus
            this.lm.set_menu(true);
            
            // show / hide details controls
            this.lm.init_lm_content_details();
            
            // draw chart
            this.create_chart();    
        }
    };
    
    // get color
    this.get_color = function (secondary_channels_colors) {
        var tmp;
        // color
        switch (this.cfg.chart_base_color) {
            case 1:
                tmp = "#ff" + secondary_channels_colors + secondary_channels_colors; 
                break;
            case 2:
                tmp = "#" + secondary_channels_colors + "ff" + secondary_channels_colors; 
                break;
            case 3:
            default:
                tmp = "#" + secondary_channels_colors + secondary_channels_colors + "ff"; 
                break;
        }
        // return color
        return tmp;        
    };
    
    // get series colors (for matrix)
    this.get_series_colors = function (num_series, base_color) {
        var colors = new Array();
        var tmp;
        for (var k=0; k<this.cfg.matrix_num_series_limit; k++) {
            // build non base channel value
            if (k > 0) {
                tmp = (256 - Math.floor((parseFloat(k) / parseFloat((this.cfg.matrix_num_series_limit - 1))) * 256)).toString(16);
                while (tmp.length < 2) {
                    tmp = "0" + tmp; 
                }
            } else {
                tmp = "00";
            }
            // store color
            colors[k] = this.get_color(tmp);            
        }
        // return colors
        return colors;    
    };
    
    //Convert unixtime to time
    this.timeConverter = function (UNIX_timestamp){
    var a = new Date(UNIX_timestamp*1000);
    var months = <?php print_string('completion_completed_on_tooltip_months', 'block_gismo'); ?>;
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = a.getDate();
        var time = date+' '+month+' '+year ;
        return time;
    }


    
    // prepare data
    this.prepare_data = function () {
        // get selected items
        var selected_items = this.lm.get_selected_items();
        var prepared_data = new Array();
        var lines = new Array();
        var genseries = new Array();
        var xticks = new Array();
        var xticks_pos = new Array();
        var yticks = new Array();
        var yticks_pos = new Array();
        var item = null, num_serie = 0, date, values, count, key, tmp, k, colors, index, used_lines, used_genseries, uid, uid2;

        // build chart
        switch (this.get_full_type()) {
            case 'teacher@student-accesses':
                if (this.static_data["users"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // yticks
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            yticks.unshift(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            yticks_pos.unshift(uid);
                        }    
                    }
                    // build line
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            lines.push(new Array(this.current_analysis.data[item].timedate, $.inArray(uid, yticks_pos) + 1, this.current_analysis.data[item].numval));      
                        }
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0 && yticks.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["yticks"] = yticks;
                        prepared_data["min_date"] = this.current_analysis.extra_info.min_date;
                        prepared_data["max_date"] = this.current_analysis.extra_info.max_date;
                        prepared_data["xticks_num"] = this.current_analysis.extra_info.num_days;
                        prepared_data["xticks_min_len"] = 5;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 13;
                        prepared_data["x_label"] = "<?php print_string('timeline', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('accesses', 'block_gismo'); ?>";
                    }       
                } 
                break;
            case 'teacher@student-accesses-overview':
                if (this.static_data["users"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    date = null;
                    tmp = new Array();
                    // build line
                    for (item in this.current_analysis.data) {    
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            // sum user contribute if date already in the list, add new entry otherwise
                            date = this.current_analysis.data[item].timedate;
                            count = this.current_analysis.data[item].numval;
                            if (tmp[date] == undefined) {
                                tmp[date] = new Array(date, parseInt(count));
                            } else {
                                tmp[date][1] += parseInt(count);
                            }                            
                        }
                    }
                    values = new Array();
                    // assoc to normal array + array of values
                    if (this.util.get_assoc_array_length(tmp) > 0) {
                        for (item in tmp) {
                            lines.push(tmp[item]);
                            values.push(tmp[item][1]);
                        }        
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["min_date"] = this.current_analysis.extra_info.min_date;
                        prepared_data["max_date"] = this.current_analysis.extra_info.max_date;
                        prepared_data["xticks_num"] = this.current_analysis.extra_info.num_days;
                        prepared_data["xticks_min_len"] = 5;
                        prepared_data["yaxis_max"] = this.get_yaxis_max(values, 1);
                        prepared_data["x_label"] = "<?php print_string('timeline', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('accesses', 'block_gismo'); ?>";
                    }      
                } 
                break;
            case 'teacher@student-resources-access':
                if (this.static_data["users"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // init (set value 0 for each course student that is selected in the left menu)
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            lines.push(0);
                            xticks.push(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            yticks.push(uid);
                        }
                    }
                    // sum contributes for each resource that is selected in the left menu
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        uid2 = this.lm.get_unique_id("resources", this.current_analysis.data[item], "resid", "restype");
                        if ($.inArray(uid2, selected_items["resources"]) != -1) {
                            if ($.inArray(uid, yticks) != -1) {
                                index = $.inArray(uid, yticks);
                                lines[index] += parseInt(this.current_analysis.data[item].numval);    
                            }        
                        }    
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["xticks"] = xticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 15;
                        prepared_data["yaxis_max"] = this.get_yaxis_max(lines, 1);
                        prepared_data["x_label"] = "<?php print_string('students', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('student_resources_overview', 'block_gismo'); ?>"; //"Accesses on resources";                        
                    }
                }   
                break;
            case 'teacher@student-resources-access:users-details':
                if (this.static_data["resources"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // yticks
                    for (item in this.static_data["resources"]) {
                        uid = this.lm.get_unique_id("resources", this.static_data["resources"][item], "id", "type");
                        if ($.inArray(uid, selected_items["resources"]) != -1) {
                            yticks.unshift(this.util.intelligent_substring(this.static_data["resources"][item].name, false));
                            yticks_pos.unshift(uid);
                        }    
                    }
                    // build line
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("resources", this.current_analysis.data[item], "resid", "restype");
                        if ($.inArray(uid, selected_items["resources"]) != -1) {
                            lines.push(new Array(this.current_analysis.data[item].timedate, $.inArray(uid, yticks_pos) + 1, this.current_analysis.data[item].numval));      
                        }
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0 && yticks.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["yticks"] = yticks;
                        prepared_data["min_date"] = this.current_analysis.extra_info.min_date;
                        prepared_data["max_date"] = this.current_analysis.extra_info.max_date;
                        prepared_data["xticks_num"] = this.current_analysis.extra_info.num_days;
                        prepared_data["xticks_min_len"] = 5;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 13;
                        prepared_data["x_label"] = "<?php print_string('timeline', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('resources', 'block_gismo'); ?>";
                    }       
                }
                break;
            case "student@resources-students-overview":
            case 'teacher@resources-students-overview':
                if (this.static_data["users"].length > 0 && this.static_data["resources"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    var userid, resid, val;
                    var max;
                    // xticks / yticks
                    
		    for (item in this.static_data["resources"]) {
                        uid = this.lm.get_unique_id("resources", this.static_data["resources"][item], "id", "type");
                        if ($.inArray(uid, selected_items["resources"]) != -1) {
                            xticks.unshift(this.util.intelligent_substring(this.static_data["resources"][item].name, false));
                            xticks_pos.unshift(uid);
                        }    
                    }
		    
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            yticks.push(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            yticks_pos.push(uid);
                        }    
                    }	
		    
                    // aggregate data (keep only selected users / resources)
                    var aggregated_data = new Array();
                    for (item in this.current_analysis.data) {
                        userid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        resid = this.lm.get_unique_id("resources", this.current_analysis.data[item], "resid", "restype");
                        val = parseInt(this.current_analysis.data[item].numval);
                        key = resid + "#" + userid;
                        if ($.inArray(userid, selected_items["users"]) != -1 &&
                            $.inArray(resid, selected_items["resources"]) != -1) {
                            if (aggregated_data[key] == undefined) {
                                aggregated_data[key] = 0;    
                            }
                            aggregated_data[key] = parseInt(aggregated_data[key]) + val;
                        }
                    }
                    // max = Math.max.apply(Math, this.util.array_values(aggregated_data));
                    max = this.current_analysis.extra_info.max_value;   // MAX MUST BE ALWAYS THE SAME (MUST NOT DEPEND ON TIME RANGE)
                    // generate series
                    colors = this.get_series_colors();
                    for (item in aggregated_data) {                        
                        // evaluate serie
                        num_serie = Math.round(parseFloat(aggregated_data[item])/parseFloat(max)*(this.cfg.matrix_num_series_limit - 2)) + 1;
                        // userid & resid
                        tmp = item.split("#");
                        userid = tmp[1];
                        resid = tmp[0];
                        // lines
                        if (lines[num_serie] == undefined) {
                            lines[num_serie] = new Array();
                            genseries[num_serie] = {color: colors[num_serie], markerOptions:{style: "filledSquare"}};
                        }
                        lines[num_serie].push(
                            new Array(
                                /*
				$.inArray(userid, xticks_pos) + 1,
                                $.inArray(resid, yticks_pos) + 1,
				//*/
				$.inArray(resid, xticks_pos) + 1,
                                $.inArray(userid, yticks_pos) + 1,			
                                aggregated_data[item],
                                max
                            )
                        );    
                    }  
                    // keep only used lines
                    used_lines = new Array();
                    used_genseries = new Array();
                    for (k=0; k<this.cfg.matrix_num_series_limit;k++) {
                        if (lines[k] != undefined && genseries[k] != undefined) {
                            used_lines.push(lines[k]);
                            used_genseries.push(genseries[k]);   
                        }
                    }                    
                    // set prepared data (at least on resource must have been selected)
                    if (used_lines.length > 0 && xticks.length > 0) {
                        
			prepared_data["lines"] = used_lines;
			prepared_data["genseries"] = used_genseries;
			prepared_data["xticks"] = xticks;
			prepared_data["yticks"] = yticks;	   
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 18;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 18;
			prepared_data["x_label"] = "<?php print_string('resources', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('students', 'block_gismo'); ?>";   
			  
                    }       
                }
                break;
            case 'teacher@resources-access':
            case 'student@resources-access':
                if (this.static_data["resources"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // init (set value 0 for each course resource that is selected in the left menu)
                    for (item in this.static_data["resources"]) {
                        uid = this.lm.get_unique_id("resources", this.static_data["resources"][item], "id", "type");
                        if ($.inArray(uid, selected_items["resources"]) != -1) {
                            lines.push(0);
                            xticks.push(this.util.intelligent_substring(this.static_data["resources"][item].name, true));
                            yticks_pos.push(uid);
                        }    
                    }
                    // sum contributes for each user that is selected in the left menu
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("resources", this.current_analysis.data[item], "resid", "restype");
                        uid2 = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        if ($.inArray(uid2, selected_items["users"]) != -1) {
                            index = $.inArray(uid, yticks_pos);
                            if (index != -1) {
                                lines[index] += parseInt(this.current_analysis.data[item].numval);    
                            }        
                        }    
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["xticks"] = xticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 15;
                        prepared_data["yaxis_max"] = this.get_yaxis_max(lines, 1);
                        prepared_data["x_label"] = "<?php print_string('resources', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('accesses', 'block_gismo'); ?>";
                    }
                }
                break;
            case 'teacher@resources-access:resources-details':
                if (this.static_data["users"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // yticks
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            yticks.unshift(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            yticks_pos.unshift(uid);
                        }    
                    }
                    // build line
                    for (item in this.current_analysis.data) {                        
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            lines.push(new Array(this.current_analysis.data[item].timedate, $.inArray(uid, yticks_pos) + 1, this.current_analysis.data[item].numval));      
                        }
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0 && yticks.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["yticks"] = yticks;
                        prepared_data["min_date"] = this.current_analysis.extra_info.min_date;
                        prepared_data["max_date"] = this.current_analysis.extra_info.max_date;
                        prepared_data["xticks_num"] = this.current_analysis.extra_info.num_days;
                        prepared_data["xticks_min_len"] = 5;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 13;
                        prepared_data["x_label"] = "<?php print_string('timeline', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('students', 'block_gismo'); ?>";
                    }       
                }
                break;
            case 'teacher@assignments':
            case 'student@assignments':
                if (this.static_data["users"].length > 0 && this.static_data["assignments"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // xticks / yticks
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            xticks.push(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            xticks_pos.push(uid);
                        }    
                    }
                    for (item in this.static_data["assignments"]) {
                        uid = this.lm.get_unique_id("assignments", this.static_data["assignments"][item], "id", "type");
                        if ($.inArray(uid, selected_items["assignments"]) != -1) {
                            yticks.unshift(this.util.intelligent_substring(this.static_data["assignments"][item].name, true));
                            yticks_pos.unshift(uid);
                        }    
                    }
                    // generate series only for selected users / assignments
                    colors = this.get_series_colors();
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        uid2 = this.lm.get_unique_id("assignments", this.current_analysis.data[item], "test_id");
                        if ($.inArray(uid, selected_items["users"]) != -1 &&
                            $.inArray(uid2, selected_items["assignments"]) != -1) {
                            
                            // evaluate serie
                            num_serie = 0;
                            if (parseInt(this.current_analysis.data[item].user_grade) != -1) { 
                                num_serie = Math.round(parseFloat(this.current_analysis.data[item].user_grade)/parseFloat(this.current_analysis.data[item].test_max_grade)*(this.cfg.matrix_num_series_limit - 2)) + 1;
                            } else {
                                if (parseInt(this.current_analysis.data[item].test_max_grade) != 0) {
                                    num_serie = 0;
                                } else {
                                    num_serie = this.cfg.matrix_num_series_limit + 10;
                                }
                            }
                            
                            // lines
                            if (lines[num_serie] == undefined) {
                                lines[num_serie] = new Array();
                                genseries[num_serie] = {color: (colors[num_serie] != undefined) ? colors[num_serie] : "#CCCCCC", markerOptions:{style: (num_serie != 0) ? "filledSquare" : "square"}};
                            }
                            lines[num_serie].push(
                                new Array(
                                    $.inArray(uid, xticks_pos) + 1,
                                    $.inArray(uid2, yticks_pos) + 1,
                                    (parseInt(this.current_analysis.data[item].user_grade) == -1) ? ((parseInt(this.current_analysis.data[item].test_max_grade) !== 0) ? "Grade has not been assigned yet." : "There isn't any grade scale associated to the assignment.") : "Grade: " + this.current_analysis.data[item].user_grade_label
                                )
                            );
                        }    
                    }
                    // keep only used lines
                    used_lines = new Array();
                    used_genseries = new Array();
                    for (k=0; k<genseries.length;k++) {
                        if (lines[k] != undefined && genseries[k] != undefined) {
                            used_lines.push(lines[k]);
                            used_genseries.push(genseries[k]);   
                        }
                    }
                    
                    // set prepared data (at least on resource must have been selected)
                    if (used_lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = used_lines;
                        prepared_data["genseries"] = used_genseries;
                        prepared_data["xticks"] = xticks;
                        prepared_data["yticks"] = yticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 18;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 18;
                        prepared_data["x_label"] = "<?php print_string('students', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('assignments', 'block_gismo'); ?>";
                    }       
                }
                break;    
            case 'teacher@assignments22':
            case 'student@assignments22':
                if (this.static_data["users"].length > 0 && this.static_data["assignments22"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // xticks / yticks
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            xticks.push(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            xticks_pos.push(uid);
                        }    
                    }
                    for (item in this.static_data["assignments22"]) {
                        uid = this.lm.get_unique_id("assignments22", this.static_data["assignments22"][item], "id", "type");
                        if ($.inArray(uid, selected_items["assignments22"]) != -1) {
                            yticks.unshift(this.util.intelligent_substring(this.static_data["assignments22"][item].name, true));
                            yticks_pos.unshift(uid);
                        }    
                    }
                    // generate series only for selected users / assignments22
                    colors = this.get_series_colors();
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        uid2 = this.lm.get_unique_id("assignments22", this.current_analysis.data[item], "test_id");
                        if ($.inArray(uid, selected_items["users"]) != -1 &&
                            $.inArray(uid2, selected_items["assignments22"]) != -1) {
                            
                            // evaluate serie
                            num_serie = 0;
                            if (parseInt(this.current_analysis.data[item].user_grade) != -1) { 
                                num_serie = Math.round(parseFloat(this.current_analysis.data[item].user_grade)/parseFloat(this.current_analysis.data[item].test_max_grade)*(this.cfg.matrix_num_series_limit - 2)) + 1;
                            } else {
                                if (parseInt(this.current_analysis.data[item].test_max_grade) != 0) {
                                    num_serie = 0;
                                } else {
                                    num_serie = this.cfg.matrix_num_series_limit + 10;
                                }
                            }
                            
                            // lines
                            if (lines[num_serie] == undefined) {
                                lines[num_serie] = new Array();
                                genseries[num_serie] = {color: (colors[num_serie] != undefined) ? colors[num_serie] : "#CCCCCC", markerOptions:{style: (num_serie != 0) ? "filledSquare" : "square"}};
                            }
                            lines[num_serie].push(
                                new Array(
                                    $.inArray(uid, xticks_pos) + 1,
                                    $.inArray(uid2, yticks_pos) + 1,
                                    (parseInt(this.current_analysis.data[item].user_grade) == -1) ? ((parseInt(this.current_analysis.data[item].test_max_grade) !== 0) ? "Grade has not been assigned yet." : "There isn't any grade scale associated to the assignment.") : "Grade: " + this.current_analysis.data[item].user_grade_label
                                )
                            );
                        }    
                    }
                    // keep only used lines
                    used_lines = new Array();
                    used_genseries = new Array();
                    for (k=0; k<genseries.length;k++) {
                        if (lines[k] != undefined && genseries[k] != undefined) {
                            used_lines.push(lines[k]);
                            used_genseries.push(genseries[k]);   
                        }
                    }
                    
                    // set prepared data (at least on resource must have been selected)
                    if (used_lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = used_lines;
                        prepared_data["genseries"] = used_genseries;
                        prepared_data["xticks"] = xticks;
                        prepared_data["yticks"] = yticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 18;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 18;
                        prepared_data["x_label"] = "<?php print_string('students', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('assignments22', 'block_gismo'); ?>";
                    }       
                }
                break;
            case 'teacher@quizzes':
            case 'student@quizzes':
                if (this.static_data["users"].length > 0 && this.static_data["quizzes"].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // xticks / yticks
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            xticks.push(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            xticks_pos.push(uid);
                        }    
                    }
                    for (item in this.static_data["quizzes"]) {
                        uid = this.lm.get_unique_id("quizzes", this.static_data["quizzes"][item], "id", "type");
                        if ($.inArray(uid, selected_items["quizzes"]) != -1) {
                            yticks.unshift(this.util.intelligent_substring(this.static_data["quizzes"][item].name, true));
                            yticks_pos.unshift(uid);
                        }    
                    }
                    // generate series only for selected users / quizzes
                    colors = this.get_series_colors();
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        uid2 = this.lm.get_unique_id("quizzes", this.current_analysis.data[item], "test_id");
                        if ($.inArray(uid, selected_items["users"]) != -1 &&
                            $.inArray(uid2, selected_items["quizzes"]) != -1) {
                            
                            // evaluate serie
                            num_serie = 0;
                            if ((parseInt(this.current_analysis.data[item].user_grade) != -1)) { 
                                num_serie = Math.round(parseFloat(this.current_analysis.data[item].user_grade)/parseFloat(this.current_analysis.data[item].test_max_grade)*(this.cfg.matrix_num_series_limit - 2)) + 1;
                            }
                            
                            // lines
                            if (lines[num_serie] == undefined) {
                                lines[num_serie] = new Array();
                                genseries[num_serie] = {color: colors[num_serie], markerOptions:{style: (num_serie != 0) ? "filledSquare" : "square"}};
                            }
                            lines[num_serie].push(
                                new Array(
                                    $.inArray(uid, xticks_pos) + 1,
                                    $.inArray(uid2, yticks_pos) + 1,
                                    (parseInt(this.current_analysis.data[item].user_grade) == -1) ? "Grade has not been assigned yet." : "Grade: " + this.current_analysis.data[item].user_grade_label,
                                    this.current_analysis.data[item].test_max_grade)
                            );       
                        }    
                    }
                    // keep only used lines
                    used_lines = new Array();
                    used_genseries = new Array();
                    for (k=0; k<this.cfg.matrix_num_series_limit;k++) {
                        if (lines[k] != undefined && genseries[k] != undefined) {
                            used_lines.push(lines[k]);
                            used_genseries.push(genseries[k]);   
                        }
                    }
                    
                    // set prepared data (at least on resource must have been selected)
                    if (used_lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = used_lines;
                        prepared_data["genseries"] = used_genseries;
                        prepared_data["xticks"] = xticks;
                        prepared_data["yticks"] = yticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 18;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 18;
                        prepared_data["x_label"] = "<?php print_string('students', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('quizzes', 'block_gismo'); ?>";
                    }       
                }
                break;
            case 'teacher@chats':
            case 'teacher@forums':
            case 'teacher@wikis':
                if (this.static_data["users"].length > 0 && this.static_data[this.current_analysis.type].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // series
                    lines[0] = new Array();
                    lines[1] = new Array();
                    // init (set value 0 for each course student that is selected in the left menu)
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            lines[0].push(0);
                            lines[1].push(0);
                            xticks.push(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            yticks.push(uid);
                        }    
                    }
                    // sum contributes for each activity that is selected in the left menu
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        uid2 = this.lm.get_unique_id(this.current_analysis.type, this.current_analysis.data[item], "actid");
                        if ($.inArray(uid2, selected_items[this.current_analysis.type]) != -1) {
                            index = $.inArray(uid, yticks);
                            if (index != -1) {
                                if (this.current_analysis.data[item].context == "sent" || this.current_analysis.data[item].context == "created"  || this.current_analysis.data[item].context == "updated") {
                                    lines[0][index] += parseInt(this.current_analysis.data[item].numval);
                                } else if (this.current_analysis.data[item].context == "viewed") {//Check if Read SUM
                                    lines[1][index] += parseInt(this.current_analysis.data[item].numval);
                                }/* else if (this.current_analysis.data[item].context == "deleted") {//Check if Delete SUBTRACT DISABLED
                                    lines[1][index] -= parseInt(this.current_analysis.data[item].numval);
                                }*/
                            }        
                        }    
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["genseries"] = new Array({label: 'Write & Update', color: this.get_color("00")}, {label: 'Read', color: "#CCCCCC"});
                        prepared_data["xticks"] = xticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 15;
                        prepared_data["yaxis_max"] = this.get_yaxis_max(lines, 2);
                        prepared_data["x_label"] = "<?php print_string('students', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('actions_on', 'block_gismo'); ?>" + this.current_analysis.type;     //"action on" + this.current_analysis.type; 
                    }
                }
                break;
            case 'teacher@chats-over-time':
            case 'teacher@forums-over-time':
            case 'teacher@wikis-over-time':
            case 'student@chats-over-time':
            case 'student@forums-over-time':
            case 'student@wikis-over-time':
                var ft = this.get_full_type();
                var spec_info = {
                    'teacher@chats-over-time': {'static': 'chats'},
                    'teacher@forums-over-time': {'static': 'forums'},
                    'teacher@wikis-over-time': {'static': 'wikis'},
                    'student@chats-over-time': {'static': 'chats'},
                    'student@forums-over-time': {'static': 'forums'},
                    'student@wikis-over-time': {'static': 'wikis'}
                };
                if (this.static_data["users"].length > 0 && this.static_data[spec_info[ft]["static"]].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    date = null;
                    tmp = new Array();
                    // build line
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        uid2 = this.lm.get_unique_id(spec_info[ft]["static"], this.current_analysis.data[item], "actid");
                        if ($.inArray(uid, selected_items["users"]) != -1 &&
                            $.inArray(uid2, selected_items[spec_info[ft]["static"]]) != -1) {
                            // sum user contribute if date already in the list, add new entry otherwise
                            date = this.current_analysis.data[item].timedate;
                            count = this.current_analysis.data[item].numval;
                            if (tmp[date] == undefined) {
                                tmp[date] = new Array(date, parseInt(count));
                            } else {
                                tmp[date][1] += parseInt(count);
                            }                            
                        }
                    }
                    values = new Array();
                    // assoc to normal array + array of values
                    if (this.util.get_assoc_array_length(tmp) > 0) {
                        for (item in tmp) {
                            lines.push(tmp[item]);
                            values.push(tmp[item][1]);
                        }        
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["min_date"] = this.current_analysis.extra_info.min_date;
                        prepared_data["max_date"] = this.current_analysis.extra_info.max_date;
                        prepared_data["xticks_num"] = this.current_analysis.extra_info.num_days;
                        prepared_data["xticks_min_len"] = 5;
                        prepared_data["yaxis_max"] = this.get_yaxis_max(values, 1);
                        prepared_data["x_label"] = "<?php print_string('timeline', 'block_gismo'); ?>";
                        prepared_data["y_label"] = "<?php print_string('nr_submissions', 'block_gismo'); ?>"; // "Number of submissions";
                    }      
                }
                break;
            case 'teacher@chats:users-details':
            case 'teacher@forums:users-details':
            case 'teacher@wikis:users-details':
            case 'student@chats':
            case 'student@forums':
            case 'student@wikis':
                if (this.static_data["users"].length > 0 && this.static_data[this.current_analysis.type].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // series
                    lines[0] = new Array();
                    lines[1] = new Array();
                    // init (set value 0 for each activity that is selected in the left menu)
                    for (item in this.static_data[this.current_analysis.type]) {
                        uid = this.lm.get_unique_id(this.current_analysis.type, this.static_data[this.current_analysis.type][item], "id", "type");
                        if ($.inArray(uid, selected_items[this.current_analysis.type]) != -1) {
                            lines[0].push(0);
                            lines[1].push(0);
                            xticks.push(this.util.intelligent_substring(this.static_data[this.current_analysis.type][item].name, false));
                            yticks.push(uid);
                        }
                    }
                    // sum contributes for each activity that is selected in the left menu
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id(this.current_analysis.type, this.current_analysis.data[item], "actid");
                        if ($.inArray(uid, selected_items[this.current_analysis.type]) != -1) {
                            index = $.inArray(uid, yticks);
                            if (index != -1) {
                                if (this.current_analysis.data[item].context == "created" || this.current_analysis.data[item].context == "sent"  || this.current_analysis.data[item].context == "updated") {
                                    lines[0][index] += parseInt(this.current_analysis.data[item].numval);
                                }  else if (this.current_analysis.data[item].context == "viewed") {//Check if Read SUM
                                    lines[1][index] += parseInt(this.current_analysis.data[item].numval);
                                } /*else if (this.current_analysis.data[item].context == "delete") {//Check if Delete SUBTRACT
                                    lines[1][index] -= parseInt(this.current_analysis.data[item].numval);
                                }*/
                            }        
                        }    
                    }
                    // set prepared data (at least on resource must have been selected)
                    if (lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = lines;
                        prepared_data["genseries"] = new Array({label: 'Write & Update', color: this.get_color("00")}, {label: 'Read', color: "#CCCCCC"});
                        prepared_data["xticks"] = xticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 15;
                        prepared_data["yaxis_max"] = this.get_yaxis_max(lines, 2);
                        prepared_data["x_label"] = this.util.ucfirst(this.current_analysis.type);
                        prepared_data["y_label"] = "<?php print_string('actions_on', 'block_gismo'); ?>" + this.current_analysis.type; // "Actions on " + this.current_analysis.type;                        
                    }
                }
                break;            
            case 'teacher@completion-assignments':
            case 'student@completion-assignments':                
                var completion_type = 'assignments';
                var completion_chart_y_label = "<?php print_string('assignments', 'block_gismo'); ?>";     
            case 'teacher@completion-assignments22':
            case 'student@completion-assignments22':  
                if(typeof completion_type === 'undefined'){         
                    var completion_type = 'assignments22';
                    var completion_chart_y_label = "<?php print_string('assignments22', 'block_gismo'); ?>";   
                }
            case 'teacher@completion-resources':
            case 'student@completion-resources':
                if(typeof completion_type === 'undefined'){                    
                    var completion_type = 'resources';
                    var completion_chart_y_label = "<?php print_string('resources', 'block_gismo'); ?>";
                }
            case 'teacher@completion-chats':
            case 'student@completion-chats':
                if(typeof completion_type === 'undefined'){                    
                    var completion_type = 'chats';
                    var completion_chart_y_label = "<?php print_string('chats', 'block_gismo'); ?>";
                }
            case 'teacher@completion-wikis':
            case 'student@completion-wikis':
                if(typeof completion_type === 'undefined'){                    
                    var completion_type = 'wikis';
                    var completion_chart_y_label = "<?php print_string('wikis', 'block_gismo'); ?>";
                }
            case 'teacher@completion-forums':
            case 'student@completion-forums':
                if(typeof completion_type === 'undefined'){                    
                    var completion_type = 'forums';
                    var completion_chart_y_label = "<?php print_string('forums', 'block_gismo'); ?>";
                }
            case 'teacher@completion-quizzes':
            case 'student@completion-quizzes':
                if(typeof completion_type === 'undefined'){
                    var completion_type = 'quizzes';
                    var completion_chart_y_label = "<?php print_string('quizzes', 'block_gismo'); ?>";
                }
                
                if (this.static_data["users"].length > 0 && this.static_data[completion_type].length > 0 && this.util.get_assoc_array_length(this.current_analysis.data) > 0) {
                    // xticks / yticks
                    for (item in this.static_data["users"]) {
                        uid = this.lm.get_unique_id("users", this.static_data["users"][item], "id", "type");
                        if ($.inArray(uid, selected_items["users"]) != -1) {
                            xticks.push(this.util.intelligent_substring(this.static_data["users"][item].name, false));
                            xticks_pos.push(uid);
                        }
                    }
                    for (item in this.static_data[completion_type]) {
                        uid = this.lm.get_unique_id(completion_type, this.static_data[completion_type][item], "id", "type");
                        if ($.inArray(uid, selected_items[completion_type]) != -1) {
                            yticks.unshift(this.util.intelligent_substring(this.static_data[completion_type][item].name, false));
                            yticks_pos.unshift(uid);
                        }    
                    }
                    // generate series only for selected users / completion_type
                    colors = this.get_series_colors();                    
                    for (item in this.current_analysis.data) {
                        uid = this.lm.get_unique_id("users", this.current_analysis.data[item], "userid");
                        if(completion_type == 'resources'){ //if resources check the correct type for resources unique_id
                            uid2 = this.lm.get_unique_id(this.current_analysis.data[item]["type"], this.current_analysis.data[item], "item_id");
                        }else{
                            uid2 = this.lm.get_unique_id(completion_type, this.current_analysis.data[item], "item_id");
                        }
                        if ($.inArray(uid, selected_items["users"]) != -1 &&
                            $.inArray(uid2, selected_items[completion_type]) != -1) {                            
                            // evaluate serie
                            num_serie = 0;                                                        
                            // lines
                            if (lines[num_serie] == undefined) {
                                lines[num_serie] = new Array();
                                genseries[num_serie] = {color: colors[num_serie], markerOptions:{style: "filledSquare" }};
                            }
                            lines[num_serie].push(
                                new Array(
                                    $.inArray(uid, xticks_pos) + 1,
                                    $.inArray(uid2, yticks_pos) + 1,
                                    ((parseInt(this.current_analysis.data[item].completionstate) == 1) || (parseInt(this.current_analysis.data[item].completionstate) == 2)) ? "<?php print_string('completion_completed_on_tooltip', 'block_gismo'); ?>" + this.timeConverter(this.current_analysis.data[item].timemodified) : "Failed", //completionstate = 1 OR 2 -> complete AND completionstate = 3 -> failed
                                    this.current_analysis.data[item].completionstate)
                            );       
                        }    
                    }                                        
                    // keep only used lines
                    used_lines = new Array();
                    used_genseries = new Array();
                    if (lines[0] != undefined && genseries[0] != undefined) { //Check if exist insert values, if not empty array
                        used_lines.push(lines[0]);
                        used_genseries.push(genseries[0]); 
                    }
                   
                    
                    // set prepared data (at least on resource must have been selected)
                    if (used_lines.length > 0 && xticks.length > 0) {
                        prepared_data["lines"] = used_lines;
                        prepared_data["genseries"] = used_genseries;
                        prepared_data["xticks"] = xticks;
                        prepared_data["yticks"] = yticks;
                        prepared_data["xticks_num"] = xticks.length;
                        prepared_data["xticks_min_len"] = 18;
                        prepared_data["yticks_num"] = yticks.length;
                        prepared_data["yticks_min_len"] = 18;
                        prepared_data["x_label"] = "<?php print_string('students', 'block_gismo'); ?>";
                        prepared_data["y_label"] = completion_chart_y_label;
                    }       
                    
                }
                break;
                
             
        }
        
        // save prepared data
        this.current_analysis.prepared_data = prepared_data;    
    };
    
    // this method returns the Y axis max
    this.get_yaxis_max = function (series, num_series) {
        var result = null;
        var max, k, limit = 10;
        if ($.isArray(series) && series.length > 0 && num_series >= 1) {
            if (num_series > 1) {
                max = new Array();
                for (k=0; k<series.length; k++) {
                    if ($.isArray(series[k]) && series[k].length > 0) {
                        max.push(Array.max(series[k]));    
                    }
                }
                if (max.length > 0) {
                    k = Array.max(max);
                    if (k < limit) {
                        result = k + 1;
                    }
                }
            } else if (num_series == 1) {
                k = Array.max(series);
                if (k < limit) {
                    result = k + 1;
                }
            }
        }
        return result;
    };
                
    // this method sets the correct plot area width and height
    this.set_plot_dimensions = function () {
        if (this.current_analysis.status == true) {
            // width
            var visible_width = $("body").width() - $("#" + this.left_menu_id).width() - 40;
            var w = visible_width;
            if (this.current_analysis.prepared_data.xticks_num != undefined) {
                var plot_width = $("#" + this.plot_id).width();
                var required_width = this.current_analysis.prepared_data.xticks_min_len*this.current_analysis.prepared_data.xticks_num + 50;
                w = (required_width < visible_width) ? visible_width : required_width;
                $("#" + this.plot_id).width(w);    
            }
            $("#" + this.plot_id).width(w);
            // height
            var visible_height = $("body").height() - $("#" + this.header_id).height() - $("#" + this.ch_header_id).height() - $("#" + this.footer_id).height() - 40;
            // var visible_height = $("#" + this.chart_id).height() - $("#" + this.ch_header_id).height() - 40;
            var h = visible_height;
            if (this.current_analysis.prepared_data.yticks_num != undefined) {
                var plot_height = $("#" + this.plot_id).height();
                var required_height = this.current_analysis.prepared_data.yticks_min_len*this.current_analysis.prepared_data.yticks_num + 50;
                h = (required_height < visible_height) ? visible_height : required_height;    
            }
            $("#" + this.plot_id).height(h);
        }    
    };
    
    // this method retrieve number of pixel available for matrix entry
    this.get_matrix_entry_side_pixels = function () {
        var num_pixel = 12;
        if (this.current_analysis.status == true) {
            // evaluate number of pixels
            var w = parseInt(parseFloat($("#" + this.plot_id).width() - 200.0) / this.current_analysis.prepared_data.xticks.length) - 4.0;
            var h = parseInt(parseFloat($("#" + this.plot_id).height() - 200.0)/ this.current_analysis.prepared_data.yticks.length) - 4.0;
            num_pixel = (w < h) ? w : h;           
        }
        return num_pixel;    
    };
    
    // create chart method
    this.create_chart = function () {
        // prepare data
        this.prepare_data();
        var data = this.current_analysis.prepared_data;
        // check data
        var missing_data = (data == undefined || data == null || !(this.util.get_assoc_array_length(data) > 0));
        // set title
        var links = (this.current_analysis.links != null) ? this.current_analysis.links : "";
        $("#" + this.ch_header_id + " #title").html(this.current_analysis.name + links);
        if (!missing_data) {
            $("#" + this.ch_header_id + " #title").html(this.current_analysis.name + links).append(
                $("<a></a>")
                    .css({margin: "0 5px"})
                    .attr({href: "javascript:void(0);"})
                    .append(
                        $("<img></img>").attr({src: "images/disk.png", alt: "<?php print_string('export_chart_as_image', 'block_gismo'); ?>", "title": "<?php print_string('export_chart_as_image', 'block_gismo'); ?>"}).css({"margin-top": "6px"}) //8.10.2013 Added translation
                    )
                    .click(
                        function () {
                            g.save_as_image(); 
                            $(this).blur();
                        }
                    )
            );
        }
        // check data
        if (missing_data) {
            // update status
            this.current_analysis.status = false;
            // show error
            this.show_error("<?php print_string('err_missing_data', 'block_gismo'); ?>", "<?php print_string('err_no_data', 'block_gismo'); ?>");  //8.10.2013 Added translation     
        } else {        
            // update status
            this.current_analysis.status = true;
            // set plot dimensions
            this.set_plot_dimensions();
            // hide welcome page
            $("#welcome_page").hide();
            // hide message
            $("#" + this.error_message_id).hide();
            // hide processing
            $("#" + this.processing_id).hide();
            // show current plot
            $("#" + this.plot_id).show();
            // build chart
            switch (this.get_full_type()) {
                case 'teacher@student-accesses':
                case 'teacher@student-resources-access:users-details':
                case 'teacher@resources-access:resources-details':
                    this.current_analysis.plot = $.jqplot(this.plot_id, [data.lines], {
                        title: {
                            show: true,
                            text: this.current_analysis.name,
                            fontSize: '18px'
                        },
                        axes:{
                            xaxis:{
                                renderer:$.jqplot.DateAxisRenderer, 
                                min: data.min_date,
                                max: data.max_date,
                                label: data.x_label,
                                labelRenderer: $.jqplot.CanvasAxisLabelRenderer, 
                                tickInterval: '1 month',
                                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                                tickOptions: {
                                    formatString:'%#d %b %Y'
                                    /*, angle: -90 */
                                },
                                autoscale: false
                            },
                            yaxis: {
                                autoscale: true,
                                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                ticks: data.yticks,
                                label: data.y_label,
                                renderer: $.jqplot.CategoryAxisRenderer,
                                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                                tickOptions: {
                                    formatString: '<nobr>%s</nobr>'
                                }
                            }
                        },
                        seriesDefaults: {
                            pointLabels: {show: false},
                            color: this.get_color("00"),
                            showLine: false,  
                            markerOptions:{style:'filledSquare', size:3, shadow: false}
                        },
                        highlighter: {
                            show: true,
                            tooltipAxes: 'xy',
                            tooltipFade: false, 
                            yvalues: 2, 
                            useAxesFormatters: true, 
                            sizeAdjust: 2, 
                            tooltipLocation: 'n',
                            tooltipOffset: 2,
                            formatString:'<div class="charts_tooltip">%s, <span class="hidden">%s</span>%s <?php print_string('accesses_tooltip', 'block_gismo'); ?></div>'
                        }
                    });
                    break;
                case 'teacher@student-accesses-overview':
                case 'teacher@chats-over-time':
                case 'teacher@forums-over-time':
                case 'teacher@wikis-over-time':
                case 'student@chats-over-time':
                case 'student@forums-over-time':
                case 'student@wikis-over-time':
                    this.current_analysis.plot = $.jqplot(this.plot_id, [data.lines], {
                        title: {
                            show: true,
                            text: this.current_analysis.name,
                            fontSize: '18px'
                        },
                        axes:{
                            xaxis:{
                                renderer:$.jqplot.DateAxisRenderer, 
                                min: data.min_date,
                                max: data.max_date,
                                label: data.x_label,
                                labelRenderer: $.jqplot.CanvasAxisLabelRenderer, 
                                tickInterval: '1 month',
                                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                                tickOptions: {
                                    formatString:'%#d %b %Y'
                                },
                                autoscale: false
                            },
                            yaxis: {
                              rendererOptions: { 
                                forceTickAt0: true 
                              },
                              padMin: 0,
                              label: data.y_label,
                              labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                              min: (data.yaxis_max == null) ? null : 0,
                              max: data.yaxis_max,
                              tickInterval: (data.yaxis_max == null) ? null : 1
                            }
                        },
                        seriesDefaults: {
                            renderer:$.jqplot.BarRenderer,
                            rendererOptions:{
                                barPadding: 0,
                                barMargin: 3,
                                barWidth: 3
                            },
                            showMarker:false,
                            pointLabels: {show: false},
                            color: this.get_color("00"),
                            shadow: false
                        },
                        highlighter: {
                            show: true,
                            tooltipAxes: 'xy',
                            tooltipFade: false, 
                            yvalues: 2, 
                            useAxesFormatters: true, 
                            sizeAdjust: 2, 
                            tooltipLocation: 'n',
                            tooltipOffset: 6,
                            formatString:'<div class="charts_tooltip">%s, %s</div>'
                        }
                    });
                    break;
                case 'teacher@student-resources-access':
                    this.current_analysis.plot = $.jqplot(this.plot_id, [data.lines], {
                      title: {
                            show: true,
                            text: this.current_analysis.name,
                            fontSize: '18px'
                        },
                      seriesDefaults: {
                        renderer:$.jqplot.BarRenderer,
                        rendererOptions:{
                            barPadding: 3,
                            barMargin: 5
                            /* barWidth: 8 */
                        },
                        showMarker:false,
                        pointLabels: {
                            show: true,
                            hideZeros: true,
                            ypadding: 2,
                            labelsFromSeries: true
                        },
                        color: this.get_color("00"),
                        shadow: false
                      },
                      axes: {
                        xaxis: {
                          renderer: $.jqplot.CategoryAxisRenderer,
                          label: data.x_label,
                          labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                          ticks: data.xticks,
                          tickOptions: {
                            angle: -90
                          }
                        },
                        yaxis: {
                          rendererOptions: { 
                            forceTickAt0: true 
                          },
                          padMin: 0,
                          label: data.y_label,
                          labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                          min: (data.yaxis_max == null) ? null : 0,
                          max: data.yaxis_max,
                          tickInterval: (data.yaxis_max == null) ? null : 1
                        }
                      },
                      highlighter: {show: false}
                    });
                    break;
                case 'teacher@resources-access':
                case 'student@resources-access':
                    this.current_analysis.plot = $.jqplot(this.plot_id, [data.lines], {
                      title: {
                            show: true,
                            text: this.current_analysis.name,
                            fontSize: '18px'
                        },
                      seriesDefaults: {
                        renderer:$.jqplot.BarRenderer,
                        rendererOptions:{
                            barPadding: 3,
                            barMargin: 5
                            /* barWidth: 8 */
                        },
                        showMarker:false,
                        pointLabels: {
                            show: true,
                            hideZeros: true,
                            ypadding: 2,
                            labelsFromSeries: true
                        },
                        color: this.get_color("00"),
                        shadow: false
                      },
                      axes: {
                        xaxis: {
                          renderer: $.jqplot.CategoryAxisRenderer,
                          label: data.x_label,
                          labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                          ticks: data.xticks,
                          tickOptions: {
                            angle: -90
                          }
                        },
                        yaxis: {
                          rendererOptions: { 
                            forceTickAt0: true 
                          },
                          padMin: 0,
                          label: data.y_label,
                          labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                          min: (data.yaxis_max == null) ? null : 0,
                          max: data.yaxis_max,
                          tickInterval: (data.yaxis_max == null) ? null : 1
                        }
                      },
                      highlighter: {show: false}
                    });
                    break;
                case 'student@resources-students-overview':
                case 'teacher@resources-students-overview':
                case 'teacher@assignments22':
                case 'teacher@assignments':
                case 'teacher@quizzes':                
                case 'student@assignments22':                
                case 'student@assignments':
                case 'student@quizzes':              
                case 'teacher@completion-quizzes':
                case 'student@completion-quizzes':             
                case 'teacher@completion-assignments':
                case 'student@completion-assignments':          
                case 'teacher@completion-assignments22':
                case 'student@completion-assignments22':                         
                case 'teacher@completion-resources':
                case 'student@completion-resources':                          
                case 'teacher@completion-forums':
                case 'student@completion-forums':                          
                case 'teacher@completion-chats':
                case 'student@completion-chats':                          
                case 'teacher@completion-wikis':
                case 'student@completion-wikis':
                    var msize = this.get_matrix_entry_side_pixels();
                    var formatString;
                    var yvalues;
                    // highlight templates
                    switch (this.get_full_type()) {
                        case "student@resources-students-overview":
                        case "teacher@resources-students-overview":
                            formatString = '<div class="charts_tooltip"><span class="hidden">%s</span>%s (max is %s)</div>';
                            yvalues = 3;
                            break;
                        default:
                            formatString = '<div class="charts_tooltip"><span class="hidden">%s</span>%s</div>';
                            yvalues = 2;
                            break;
                    }
                    this.current_analysis.plot = $.jqplot(this.plot_id, data.lines, {
                        title: {
                            show: true,
                            text: this.current_analysis.name,
                            fontSize: '18px'
                        },
                        seriesDefaults: {pointLabels: {show: false}, showLine: false, markerOptions:{size: msize, shadow: false}/*, gridPadding: {top:5, right:5, bottom:5, left:5}*/},
                        series: data.genseries,
                        axes: {
                            xaxis: {
                                label: data.x_label,
                                autoscale: false,
                                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                ticks: data.xticks,
                                tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
                                tickOptions: {
                                    angle: -90
                                },
                                renderer: $.jqplot.CategoryAxisRenderer
                            },
                            yaxis: {
                                label: data.y_label,
                                autoscale: false,
                                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                ticks: data.yticks,
                                renderer: $.jqplot.CategoryAxisRenderer,
                                tickRenderer: $.jqplot.CanvasAxisTickRenderer
                            }           
                        },
                        cursor: {
                            show: false
                        },
                        highlighter: {
                            show: true,
                            tooltipAxes: 'y',
                            tooltipFade: false, 
                            yvalues: yvalues, 
                            useAxesFormatters: true, 
                            sizeAdjust: 4.5, 
                            tooltipLocation: 'n',
                            tooltipOffset: 2,
                            formatString: formatString
                        }
                    });
                    break;  
                case 'teacher@chats':
                case 'teacher@forums':
                case 'teacher@wikis':
                case 'teacher@chats:users-details':
                case 'teacher@forums:users-details':
                case 'teacher@wikis:users-details':
                case 'student@chats':
                case 'student@forums':
                case 'student@wikis':
                    this.current_analysis.plot = $.jqplot(this.plot_id, data.lines, {
                      title: {
                            show: true,
                            text: this.current_analysis.name,
                            fontSize: '18px'
                        },
                      seriesDefaults: {
                        renderer:$.jqplot.BarRenderer,
                        rendererOptions:{
                            barPadding: 3,
                            barMargin: 5
                            /* barWidth: 8 */
                        },
                        showMarker:false,
                        pointLabels: {
                            show: true,
                            hideZeros: true,
                            ypadding: 2,
                            labelsFromSeries: true
                        },
                        /*color: this.get_color("00"),*/
                        shadow: false
                      },
                      series: data.genseries,
                      legend: {
                        show: true,
                        placement: 'outsideGrid'
                      },
                      axes: {
                        xaxis: {
                          renderer: $.jqplot.CategoryAxisRenderer,
                          label: data.x_label,
                          labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                          ticks: data.xticks,
                          tickOptions: {
                            angle: -90
                          }
                        },
                        yaxis: {
                          rendererOptions: { 
                            forceTickAt0: true 
                          },
                          padMin: 0,
                          min: (data.yaxis_max == null) ? null : 0,
                          max: data.yaxis_max,
                          tickInterval: (data.yaxis_max == null) ? null : 1,
                          label: data.y_label,
                          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
                        }
                      },
                      highlighter: {show: false}
                    });
                    break;
            }
        }   
    };
    
    // update chart method
    this.update_chart = function () {
        // reset DOM
        this.reset_dom();
        // recreate chart
        this.create_chart();          
    };
    
    // resize method
    this.resize = function () {
        // adjust left menu and chart height
        var content_height = $("body").height() - $("#" + this.header_id).height() - $("#" + this.footer_id).height();
        $("#" + this.left_menu_id).height(content_height);
        $("#" + this.lm_content_id).height($("#" + this.left_menu_id).height() - $("#" + this.lm_header_id).height());
        $("#" + this.chart_id).height(content_height);
        $("#" + this.ch_content_id).height($("#" + this.chart_id).height() - $("#" + this.ch_header_id).height());
        $("#" + this.plot_id).height($("#" + this.ch_content_id).height() - parseInt($("#" + this.plot_id).css("marginTop")) - parseInt($("#" + this.plot_id).css("marginBottom")));
        // timeline width
        $("#" + this.date_slider_id).width($("body").width() - $("#" + this.from_date_id).width() - $("#" + this.to_date_id).width() - 35);    
        // redraw chart
        if (this.current_analysis != undefined && this.current_analysis.plot != undefined && this.current_analysis.plot != null) {
            if (this.resize_scheduled == false) {
                // schedule resize
                this.resize_scheduled = true;
                var g = this;
                setTimeout(function () {
                    // set plot dimensions
                    g.set_plot_dimensions();
                    // replot
                    // g.current_analysis.plot.replot({clear: true, resetAxes: false});
                    g.update_chart();
                    // resize not scheduled anymore
                    g.resize_scheduled = false;
                    g.last_resize = (new Date()).getTime();    
                }, (this.last_resize + this.cfg.resize_delay < (new Date()).getTime()) ? 5 : this.cfg.resize_delay);
            }    
        }    
    };

    this.is_item_visible = function (item) {
        var visibility = false;
        if (this.cfg.include_hidden_items == "1" || (item["visible"] != undefined && item["visible"] == "1")) {
            visibility = true;
        }
        return visibility;
    };

    this.get_items_number = function (item) {
        var count = 0;
        if (this.static_data[item] != undefined && this.static_data[item] != null) {
            count = this.static_data[item].length;
            if (count > 0 && this.cfg.include_hidden_items == "0") {
                for (var k in this.static_data[item]) {
                    if (this.static_data[item][k]["visible"] != undefined && this.static_data[item][k]["visible"] == "0") {
                        count--;
                    }
                }
            }
        }
        return count;
    };

    // save method
    this.save_as_image = function () {
        if (this.current_analysis.status == true) {
            // content to be put in the new window / tab
            var data = $($("#" + this.plot_id).jqplotToImageElem()).attr("src").replace(/^data:image\/(png|jpg);base64,/, "");
            $("#save_form #data").html(data);
            $("#save_form #chart_id").val(this.current_analysis.type);
            // submit the form
            $("#save_form").submit();
        } else {
            this.util.show_modal_dialog("<?php print_string('export_chart_as_image', 'block_gismo'); ?>", "<p><?php print_string('no_chart_at_the_moment', 'block_gismo'); ?></p>");    
        } 
    };
    
    // options
    this.options = function () {
        // self
        var g = this;
        // show options dialog
        var dialog = $("<div></div>").attr("id", "dialog");
        var form = $('<form></form>')
                    .attr({id: "gismo_options_form", name: "gismo_options_form"})
                    .append($('<fieldset></fieldset>')
                        .addClass("local_fieldset")
                        .append($("<legend></legend>").html("<?php print_string('option_general_settings', 'block_gismo'); ?>"))
                        // show hidden items
                        .append($('<label></label>').attr({"for": "include_hidden_items_yes"}).html("<?php print_string('option_include_hidden_items', 'block_gismo'); ?>"))
                        .append($('<input type="radio"></input>').attr({id: "include_hidden_items_yes", name: "include_hidden_items", value: "1"}))
                        .append("<?php print_string('yes'); ?>")
                        .append($('<input type="radio"></input>').attr({id: "include_hidden_items_no", name: "include_hidden_items", value: "0"}))
                        .append("<?php print_string('no'); ?>")
                        .append($('<br />'))
                    )
                    .append($('<fieldset></fieldset>')
                        .addClass("local_fieldset")
                        .append($("<legend></legend>").html("<?php print_string('option_chart_settings', 'block_gismo'); ?>"))
                        // base color
                        .append($('<label></label>').attr({"for": "charts_base_color_red"}).html("<?php print_string('option_base_color', 'block_gismo'); ?>"))
                        .append($('<input type="radio"></input>').attr({id: "charts_base_color_red", name: "chart_base_color", value: "1"}))
                        .append("<?php print_string('option_red', 'block_gismo'); ?>")
                        .append($('<input type="radio"></input>').attr({id: "charts_base_color_green", name: "chart_base_color", value: "2"}))
                        .append("<?php print_string('option_green', 'block_gismo'); ?>")
                        .append($('<input type="radio"></input>').attr({id: "charts_base_color_blue", name: "chart_base_color", value: "3"}))
                        .append("<?php print_string('option_blue', 'block_gismo'); ?>")
                        .append($('<br />'))
                        // Axes label max length
                        .append($('<label></label>').attr({"for": "chart_axis_label_max_len"}).html("<?php print_string('option_axes_label_max_length', 'block_gismo'); ?>"))
                        .append($('<input type="text"></input>').attr({id: "chart_axis_label_max_len", name: "chart_axis_label_max_len", maxlength: 2}).addClass("small_field"))
                        .append($('<br />'))
                        // Axes label max offset
                        .append($('<label></label>').attr({"for": "chart_axis_label_max_offset"}).html("<?php print_string('option_axes_label_max_offset', 'block_gismo'); ?>"))
                        .append($('<input type="text"></input>').attr({id: "chart_axis_label_max_offset", name: "chart_axis_label_max_offset", maxlength: 2}).addClass("small_field"))
                        .append($('<br />'))
                        // Matrix series max number
                        .append($('<label></label>').attr({"for": "matrix_num_series_limit"}).html("<?php print_string('option_number_of_colors', 'block_gismo'); ?>"))
                        .append($('<input type="text"></text>').attr({id: "matrix_num_series_limit", name: "matrix_num_series_limit", maxlength: 2}).addClass("small_field"))
                        .append($('<br />'))
                    )
                    .append($('<fieldset></fieldset>')
                        .addClass("local_fieldset")
                        .append($("<legend></legend>").html("<?php print_string('option_other_settings', 'block_gismo'); ?>"))
                        // Window resize delay
                        .append($('<label></label>').attr({"for": "resize_delay"}).html("<?php print_string('option_window_resize_delay_seconds', 'block_gismo'); ?>"))
                        .append($('<select></select>').attr({id: "resize_delay", name: "resize_delay"})
                            .addClass("medium_field")
			    .append($('<option></option>').attr({value: parseInt(0.0 * 1000.0)}).html("0.0 (no delay)"))
			    .append($('<option></option>').attr({value: parseInt(0.5 * 1000.0)}).html("0.5"))
                            .append($('<option></option>').attr({value: parseInt(1.0 * 1000.0)}).html("1.0"))
                            .append($('<option></option>').attr({value: parseInt(1.5 * 1000.0)}).html("1.5"))
                            .append($('<option></option>').attr({value: parseInt(2.0 * 1000.0)}).html("2.0"))
                            .append($('<option></option>').attr({value: parseInt(2.5 * 1000.0)}).html("2.5"))
                            .append($('<option></option>').attr({value: parseInt(3.0 * 1000.0)}).html("3.0"))
                            .append($('<option></option>').attr({value: parseInt(3.5 * 1000.0)}).html("3.5"))
                            .append($('<option></option>').attr({value: parseInt(4.0 * 1000.0)}).html("4.0"))
                            .append($('<option></option>').attr({value: parseInt(4.5 * 1000.0)}).html("4.5"))
                            .append($('<option></option>').attr({value: parseInt(5.0 * 1000.0)}).html("5.0"))
                        )
                    );           
        dialog.html("<p><?php print_string('option_intro', 'block_gismo'); ?></p>" + $('<div></div>').append(form).html());
        dialog.attr("title", "<?php print_string('options', 'block_gismo'); ?>");
        dialog.dialog({ 
            resizable: false, 
            modal: true, 
            draggable: false,
            width: 500,
            buttons: {
                '<?php print_string('cancel', 'block_gismo'); ?>': function() {
                    // close dialog 
                    $(this).dialog('destroy'); 
                },
                '<?php print_string('save', 'block_gismo'); ?>': function() {
                    var response = true;
                    // update instance config
                    g.cfg.include_hidden_items = parseInt($(this).find("input[name='include_hidden_items']:checked").val());
                    g.cfg.chart_base_color = parseInt($(this).find("input[name='chart_base_color']:checked").val());
                    g.cfg.chart_axis_label_max_len = parseInt($(this).find("#chart_axis_label_max_len").val());
                    g.cfg.chart_axis_label_max_offset = parseInt($(this).find("#chart_axis_label_max_offset").val());
                    g.cfg.matrix_num_series_limit = parseInt($(this).find("#matrix_num_series_limit").val());
                    g.cfg.resize_delay = parseInt($(this).find("#resize_delay").val());
                    // update settings
                    var config_data = "";
                    for (var k in g.cfg) {
                        config_data += "config_data[" + k + "]=" + g.cfg[k] + "&";        
                    }
                    $.ajax({
                        url: 'ajax_config.php',
                        async: false, 
                        type: 'POST',
                        data: 'q=save&' + config_data + 'srv_data=' + g.srv_data + '&token=' + Math.random(), 
                        dataType: 'json',
                        success: 
                            function(json) {
                                if (!(json["status"] != undefined && json["status"] == "true")) {
                                    response = {error: '1', message: 'Cannot save settings to the database!'};    
                                } else {
                                    response = true;
                                }
                            },
                        error:
                            function(error) {
                                response = {error: '1', message: '<?php print_string('err_unknown', 'block_gismo'); ?>'};     
                            }
                    });
                    // check response for errors
                    if (response['error'] != undefined && response['error'] == '1') {
                        if (response['message'] != undefined) {
                            g.show_error(response['message']);
                        } else {
                            g.show_error('<?php print_string('err_unknown', 'block_gismo'); ?>!');    
                        }    
                    } else {
                        // rebuild left menu
                        g.lm.init();
                        // replot the chart using new settings 
                        if (g.current_analysis.status == true) {
                            g.update_chart();
                        }
                        // set menu (visible lists icons)
                        g.lm.set_menu(false);
                    }
                    // close dialog 
                    $(this).dialog('destroy'); 
                }                            
            }
        });
        // set form values
        $("#dialog #include_hidden_items_yes").prop('checked', (g.cfg.include_hidden_items == 1));
        $("#dialog #include_hidden_items_no").prop('checked', (g.cfg.include_hidden_items == 0));
        $("#dialog #charts_base_color_red").prop('checked', (g.cfg.chart_base_color == 1));
        $("#dialog #charts_base_color_green").prop('checked', (g.cfg.chart_base_color == 2));
        $("#dialog #charts_base_color_blue").prop('checked', (g.cfg.chart_base_color == 3));
        $("#dialog #chart_axis_label_max_len").val(g.cfg.chart_axis_label_max_len);
        $("#dialog #chart_axis_label_max_offset").val(g.cfg.chart_axis_label_max_offset);
        $("#dialog #matrix_num_series_limit").val(g.cfg.matrix_num_series_limit);
        $("#dialog #resize_delay").val(g.cfg.resize_delay);                                   
    };
    
    // show content
    this.show_content = function () {
        // hide help section
        $("div#help").hide();
	$("div#short_overview").hide();
        // show content section
        $("div#app_content").show();
        // show footer
        $("div#footer").show();
    }
    
    // show help
    this.show_help = function () {
        // reset
        this.reset();
        this.current_analysis.status = false;
        // hide content section
        $("div#app_content").hide();
        // hide footer
        $("div#footer").hide();
        // show help section
	$("div#short_overview").hide();
        $("div#help").show();
    };
    
    // show short_overview
    this.show_short_overview = function () {
	<?php
	$path_teacher="";
	$path_student="";
	if(!isset($SESSION->lang)){
		$SESSION->lang=$USER->lang;
	}
	switch($SESSION->lang){
		case "de":
			$path_teacher="http://moclog.ch/de/tutorials/moclog-gismo-fur-dozenten/";
			$path_student="http://moclog.ch/de/tutorials/moclog-gismo-fur-studenten/";
		break;
		case "fr":
			$path_teacher="http://moclog.ch/fr/tutorials/moclog-gismo-pour-enseignants/";
			$path_student="http://moclog.ch/fr/tutorials/moclog-gismo-pour-etudiants/";
		break;
		case "it":
			$path_teacher="http://moclog.ch/it/tutorials/moclog-gismo-per-docenti/";
			$path_student="http://moclog.ch/it/tutorials/moclog-gismo-per-studenti/";
		break;	
		case 'en':
		default:
			$path_teacher="http://moclog.ch/tutorials/moclog-gismo-for-instructors/";
			$path_student="http://moclog.ch/tutorials/moclog-gismo-for-students/";	
		break;
	}
	?>
	if(this.actor=='teacher'){ path="<?php echo $path_teacher; ?>"; }else{ path="<?php echo $path_student; ?>"; }
        window.open(path,"_blank","toolbar=no, locaqtion=no, directories=no, swtatus=no,nemubvar=no, scrollabrs=auto, resizable=yes, copyhistory=no");
    };    
    
    this.about = function () {
        // self
        var g = this;
        // show options dialog
        var dialog = $("<div></div>").attr("id", "dialog");
        var about = $('<div></div>')
                    .append($('<fieldset></fieldset>')
                        .addClass("local_fieldset")
                        .append($("<legend></legend>").html("Gismo"))
                        .append("<?php print_string('intro_information_about_gismo', 'block_gismo'); ?>")
                        .append($("<p></p>").append($("<ul></ul>")
                            .append($("<li></li>").append("<?php print_string('gismo_version', 'block_gismo'); ?>: <?php print_string('gismo_version_value', 'block_gismo'); ?>"))
                            .append($("<li></li>").append("<?php print_string('release_date', 'block_gismo'); ?>: <?php print_string('release_date_value', 'block_gismo'); ?>"))
                        )) 
                    )
                    .append($('<fieldset></fieldset>')
                        .addClass("local_fieldset")
                        .append($("<legend></legend>").html("<?php print_string('authors', 'block_gismo'); ?>"))
                        .append("<?php print_string('contact_us', 'block_gismo'); ?>")
                        .append($("<p></p>").append($("<ul></ul>")                        
                            .append($("<li></li>").append("Christian Milani (christian.milani _AT_ usi.ch)"))
                            .append($("<li></li>").append("Riccardo Mazza (riccardo.mazza _AT_ usi.ch)"))
			    .append($("<li></li>").append("Luca Mazzola (mazzola.luca _AT_ gmail.com)"))
                            .append($("<li></li>").append("Mauro Nidola (mauro.nidola _AT_ usi.ch)"))
                        ))
                    )
        dialog.html(about.html());
        dialog.attr("title", "<?php print_string('about_gismo', 'block_gismo'); ?>");
        dialog.dialog({ 
            resizable: false, 
            modal: true, 
            draggable: false,
            width: 500,
            buttons: {
                '<?php print_string('close', 'block_gismo'); ?>': function() {
                    // close dialog 
                    $(this).dialog('destroy'); 
                }                            
            }
        });   
    };
    
    // exit
    this.exit = function () {
        return this.util.show_exit_confirmation("GISMO - Exit", "<?php print_string('confirm_exiting', 'block_gismo'); ?>");
    };
}