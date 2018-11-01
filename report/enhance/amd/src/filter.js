define(['jquery'], function($) {
 
    return {
        init: function() {
 
            // Put whatever you like here. $ is available
            // to you as normal.
            $(".filter-list .nav-link").click(function() {
                localStorage.setItem('selected-filter', $(this).attr("data-filter"));
                $(".filter-list .nav-link").removeClass("active");
                $(this).addClass("active");
                $(".filter-all").hide();
                $($(this).attr("data-filter")).show();
            });

            if (filter = localStorage.getItem('selected-filter')) {
                $(".filter-all").hide();
                $(filter).show();
            }
        }
    };
});
