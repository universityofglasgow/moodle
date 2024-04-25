require(['jquery'], function($) {
    $.noConflict();
    (function ($) {
        $(document).ready(function () {
            $('span.fa-trash').on('click', function(){
                var id = $(this).attr('id').replace('_reset_', '_');
                $('input[id="' + id + '"]:checked').prop('checked', false);
            });
        });
    })(jQuery);
});
