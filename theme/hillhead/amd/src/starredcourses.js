define(['jquery', 'jqueryui', 'theme_hillhead/jquery.ui.touch-punch-improved'], function($) {
 
    return {
        init: function() {
            
            $("#starredCourses").each(function() {
               $(this).html($(this).children('li').sort(function(a, b){
                    return ($(b).attr('order')) < ($(a).attr('order')) ? 1 : -1;
                }));
            });
            
            $("#saveSidebar").hide();
            $("#rearrangeSidebar").show();
            $("#rearrangeSidebar").click(function() {
                $("#rearrangeSidebar").hide();
                $("#saveSidebar").show();
                $("#starredCourses i").removeClass("fa-star");
                $("#starredCourses i").addClass("fa-arrows");
                $("#starredCourses").sortable({cancel: '.allcourses'});
                $("#starredCourses").disableSelection();
                $("#starredCourses li .media").each(function() {
                    var link = $(this).parent().attr("href").replace("/course/view", "/theme/hillhead/course-unstar");
                    var link = link.substr(0, link.indexOf("?"));
                    var courseid = $(this).parent().attr("href").substring($(this).parent().attr("href").lastIndexOf("=")+1);
                    var title = "Unstar " + $(this).find(".media-body").text();
                    $(this).append('<span class="media-right"><a class="unpinCourseBadge" href="#" data-getlink="'+link+'" data-courseid="'+courseid+'"  title="'+title+'"><i class="fa fa-trash"></i></a></span>');
                });
                $("#starredCourses .unpinCourseBadge").click(function() {
                    $.get($(this).attr("data-getlink"), {id: $(this).attr("data-courseid")});
                    $(this).closest("li").remove();
                });
                return false;
            });
            $("#saveSidebar").click(function() {
                var courseOrder = [];
                $("#starredCourses li").each(function() {
                    courseOrder.push(new Number($(this).attr("courseid")));
                });
                
                $.post($("#starredCourses").attr("save"), {o: courseOrder.join(',')});
                
                $("#saveSidebar").hide();
                $("#rearrangeSidebar").show();
                $("#starredCourses i").addClass("fa-star");
                $("#starredCourses i").removeClass("fa-arrows");
                 $("#starredCourses .media-right").remove();
                return false;
            });
        }
    };
});