require(['jquery'], function($) {
    $.noConflict();
    (function ($) {
        $(document).ready(function () {
            $('input[data-multimtf="0"]').each(function(){
                if ($(this).is(':checked')) {
                    var qtypemtf_whichid = $(this).attr('data-mtf');
                    var qtypemtf_whichelementid = $(this).attr('id');
                    $('input[data-hiddenmtf="' + qtypemtf_whichid + '"]').attr("disabled", false); // Remove all values from hidden.
                }
            });
            $('input[data-mtf^=qtype_mtf]').on('click', function() {
                if ( $(this).attr("data-multimtf") == 0 ) {
                    var radiomtfid = $(this).attr('id');
                    var radiomtfdatamtf = $(this).attr('data-mtf');
                    $('input[data-mtf="' + radiomtfdatamtf + '"]').prop('checked', false);
                    $('input[data-hiddenmtf="' + radiomtfdatamtf + '"]').attr("disabled", false); // Remove all values from hidden.
                    // $('input[data-hiddenmtf="'+radiomtfdatamtf+'"]').val("2"); // Remove all values from hidden.
                    $('input[id="hidden_' + radiomtfid + '"]').attr("disabled", true); // Disable this element.
                    $('input[id="' + radiomtfid + '"]').prop('checked', true); // Tick the originally clicked on radio.
                }
            });
        });
    })(jQuery);
});
// Qtype_mtf : END.

