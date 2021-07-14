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
                return false;
            });
        }
    };
});