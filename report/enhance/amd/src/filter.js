define(['jquery'], function($) {

    return {
        init: function() {

            // Put whatever you like here. $ is available
            // to you as normal.
            $(".filter-list .nav-link").click(function() {
                $(".filter-list .nav-link").removeClass("active");
                $(this).addClass("active");
                $(".filter-all").hide();
                $($(this).attr("data-filter")).show();
                if($($(this).attr("data-filter")).length == 0) {
                    $(".bottom-button").hide();
                } else {
                    $(".bottom-button").show();
                }
                try {
                    localStorage.setItem('selected-filter', $(this).attr("data-filter"));
                } catch (error) {
                    return false;
                }
            });

            try {
                var filter = localStorage.getItem('selected-filter');
                if (filter) {
                    $(".filter-all").hide();
                    $(filter).show();
                    if($(filter).length == 0) {
                        $(".bottom-button").hide();
                    } else {
                        $(".bottom-button").show();
                    }
                }
            } catch (error) {
                return false;
            }
        }
    };
});
