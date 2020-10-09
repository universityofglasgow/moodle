require(['jquery'], function($) { $(document).ready(function(){
    
    // Remove custom styles from topic sections
    
    $("li.section ul.section *").each(function() {
        $(this).removeAttr("style").removeAttr("bgcolor").removeAttr("style");
    });
    
    // Remove custom styles from section summaries
    
    $("li.section div.summary *").each(function() {
        $(this).removeAttr("style").removeAttr("bgcolor").removeAttr("style");
    });
    
    // Remove custom styles from activity intros
    
    $("body.path-mod #intro *").each(function() {
        $(this).removeAttr("style").removeAttr("bgcolor").removeAttr("style");
    });
    
})});