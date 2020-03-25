define(['jquery', 'core/templates', 'core/ajax', 'core/notification'], function($, templates, ajax, notification) {

    return {
        init: function() {

            // Set up filter for list of requests
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
                if (filter === null) {
                    filter = ".filter-status-1, .filter-status-2, .filter-status-3," +
                        " .filter-status-4, .filter-status-5, .filter-status-6, .filter-status-9";
                }
                $(".filter-all").hide();
                $(filter).show();
                if ($(filter).length == 0) {
                    $(".bottom-button").hide();
                } else {
                    $(".bottom-button").show();
                }
                $('.active').removeClass("active");
                $("[data-filter='" + filter + "']").addClass("active");
            } catch (error) {
                return false;
            }

            // Display updated voting buttons
            function render_voting(container, requestid, votes) {
                var context = {
                    votecount: votes.count,
                    id: requestid,
                    ownrequest: votes.ownrequest,
                    voted: votes.voted
                };
                templates.render("report_enhance/votes", context)
                   .done(function(html) {
                        $(container).html(html);
                    })
                    .fail(notification.exception);
            }

            // set up voting stuffs
            $(".enhance_votes").on("click", ".votebutton", function() {
                var requestid = $(this).data("requestid");
                var vote = $(this).data("vote");
                var container = $(this).parent();

                // ajax call to get voting details
                ajax.call([{
                    methodname: 'report_enhance_set_vote',
                    args: { requestid: requestid, vote: vote },
                    done: function(votes) { render_voting(container, requestid, votes);  },
                    fail: notification.exception,
                }]);

                return false;
            });

            // remember scroll position
            $(".save-link").on("click", function() {
                var scroll = $(document).scrollTop();
                try {
                    localStorage.setItem('scroll-position', scroll);
                } catch(e) {
                    return;
                }
            });

            // Restore scroll position
            try {
                var scroll = localStorage.getItem('scroll-position');
            } catch(e) {
                return;
            }
            if (scroll) {
                $(document).scrollTop(scroll);
            }
        }
    };
});
