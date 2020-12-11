/**
 * This is JavaScript code that handles mtf qtype radio buttons and form elements.
 * @package    qtype
 * @subpackage mtf
 * @copyright  ETHZ LET <amr.hourani@id.ethz.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {
    // Before ready document, disable id_numberofcolumns.
    $('#id_numberofcolumns').css("background-color", "#EEEEEE");
    $('#id_numberofcolumns').prop('disabled', true);

    // Before ready document, disable id_numberofrows.
    $('#id_numberofrows').css("background-color", "#EEEEEE");
    $('#id_numberofrows').prop('disabled', true);

    $(document).ready(function () {
        // Number of Answers.
        $('#id_numberofcolumns').on('change', function() {
              howmanyanswers = $('#id_numberofcolumns').val();
              mtftypechanged(howmanyanswers,'changed');
        });
        // Number of Qestions.
        $('#id_numberofrows').on('change', function() {
              numberofrows = $('#id_numberofrows').val();
              mtfnumberchanged(numberofrows,'changed');
        });
        // For radio one right solution only.
        $('input[data-colmtf="positive"]').on('click', function() {
              var howmanyanswers = $('#id_numberofcolumns').val();
            if( howmanyanswers == 1) {
                var radiomtfid = $(this).attr('id');
                $('input[data-colmtf="positive"]').attr('checked', false); // UN-Tick all TRUE radios.
                $('input[data-colmtf="negative"]').attr('checked', true); // Tick all FALSE radios.
                 $('#' + radiomtfid).prop('checked', true); // Tick the originally clicked on radio.
            }
        });
        mtftypechanged = function(howmanyanswers, loadorchanged){
              var mtfradionegative = 'input[data-colmtf="negative"]';
              var mtfradiopositive = 'input[data-colmtf="positive"]';

              $('#judgmentoptionsspan').show();
              $(mtfradionegative).show();
              $(mtfradionegative).parent().show(); // Show the label of radio button.
              $('#id_scoringmethod_subpoints').show();
              $('#id_scoringmethod_subpoints').parent().show();
              $('#id_scoringmethod_mtfonezero').show();
              $('#id_scoringmethod_mtfonezero').parent().show();

            if (loadorchanged == 'changed') {
                // If changed by human, then Tick subpoints LMDL-130.
                $('#id_scoringmethod_subpoints').prop('checked', true);
                // If changed by human, then LMDL-134 tick all true! Aaaah.
                $(mtfradiopositive).prop('checked', true); // Tick all TRUE radios.
            }

        };
        mtfnumberchanged = function(numberofrows, loadorchanged){

              numberofrows = parseInt(numberofrows);
              var maxmtfoptions = 15;
              var allowedtochangeresult = 1;
              var optionboxes = '#qtype_mtf_optionbox_response_';
              var lasttimerows = $("input[name=qtype_mtf_lastnumberofcols]").val();
              var remainingmtfoptions = maxmtfoptions - numberofrows;

            if (lasttimerows > numberofrows) {
                var mtfdiffraws = lasttimerows - numberofrows;
                if (confirm(M.util.get_string('deleterawswarning', 'qtype_mtf', mtfdiffraws))) {
                     allowedtochangeresult = 1;
                } else {
                    allowedtochangeresult = 0;
                    // Reset the select box to original number.
                    if (lasttimerows > maxmtfoptions) {
                        lasttimerows = maxmtfoptions;
                        alert(M.util.get_string('mustdeleteextrarows', 'qtype_mtf', mtfdiffraws))
                    }
                    $("#id_numberofrows").val(lasttimerows);
                }

            }

            if (allowedtochangeresult == 1) {
                // Set the current no of rows to choosen one.
                $("input[name=qtype_mtf_lastnumberofcols]").val(numberofrows);

                if (numberofrows < maxmtfoptions) { // If I have more but want less.
                    // Hide all the maxmtfoptions - if confirmed.
                    for (i = maxmtfoptions; i > numberofrows; i--) {
                        $(optionboxes + i).hide();
                    }
                    // Show all the numberofrows again.
                    for (i = 1; i <= numberofrows; i++) {
                        $(optionboxes + i).show();
                    }

                } else { // If I have less but want more.
                    for (i = 1; i <= maxmtfoptions; i++) {
                        $(optionboxes + i).show();
                    }
                }
            }

        };

        // Initialise the script and do magic.

        // Enable id_numberofcolumns select.
        $('#id_numberofcolumns').prop('disabled', false);
        $('#id_numberofcolumns').css("background-color", "#FFFFFF");

        // Enable id_numberofrows select.
        $('#id_numberofrows').prop('disabled', false);
        $('#id_numberofrows').css("background-color", "#FFFFFF");

        var howmanyanswers = $('#id_numberofcolumns').val();
        mtftypechanged(howmanyanswers, 'load');

        var numberofrows = $('#id_numberofrows').val();
        mtfnumberchanged(numberofrows, 'load');

        // If firsttime loading, then tick first TRUE.
        if (!$('input[name=id]').val() || $('input[name=id]').val() == '') {
              // Tick first TRUE.
              $('#id_weightbutton_0_1').prop('checked', true);
        }

    });
})(jQuery);
