require(['jquery'], function($) { $(document).ready(function(){
    
    // Remove custom styles from topic sections
    
    $("ul.weeks li.section .content *").each(function() {
        $(this).removeAttr("style").removeAttr("bgcolor").removeAttr("style");
    });
    
    $("ul.topics li.section .content *").each(function() {
        $(this).removeAttr("style").removeAttr("bgcolor").removeAttr("style");
    });
    
    $("ul.gtopics li.section .content *").each(function() {
        $(this).removeAttr("style").removeAttr("bgcolor").removeAttr("style");
    });
    
    // Remove custom styles from activity intros
    
    $("body.path-mod #intro *").each(function() {
        $(this).removeAttr("style").removeAttr("bgcolor").removeAttr("style");
    });
    
})});