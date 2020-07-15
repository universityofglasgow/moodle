/**
 * Javascript controller for opening the originality report viewreport

 * @package plagiarism_turnitin
 * @copyright 2019 Turnitin
 * @author Charlotte Spinks <cspinks@turnitin.com>
 * @module plagiarism_turnitin/open_viewer
 */

define(['jquery'], function($) {
    return {
        origreport_open: function() {
            var that = this;
            $(document).on('click', '.pp_origreport_open', function() {
                var classList = $(this).attr('class').replace(/\s+/,' ').split(' ');

                for (var i = 0; i < classList.length; i++) {
                    if (classList[i].indexOf('origreport_') !== -1 && classList[i] != 'pp_origreport_open') {
                        var classStr = classList[i].split("_");
                        that.openDV("origreport", classStr[1], classStr[2]);
                    }
                }
             });
        },

        grademark_open: function() {
            var that = this;
            $(document).on('click', '.pp_grademark_open', function() {
                var classList = $(this).attr('class').replace(/\s+/,' ').split(' ');

                for (var i = 0; i < classList.length; i++) {
                    if (classList[i].indexOf('grademark_') !== -1 && classList[i] != 'pp_grademark_open') {
                        var classStr = classList[i].split("_");
                        var url = "";
                        // URL must be stored in separate div on forums.
                        if ($('.grademark_forum_launch_' + classStr[1]).length > 0) {
                            url = $('.grademark_forum_launch_' + classStr[1]).html();
                        } else {
                            url = $(this).attr("id");
                        }
                        that.openDV("grademark", classStr[1], classStr[2], url);
                    }
                }
            });
        },

        // Open the DV in a new window in such a way as to not be blocked by popups.
        openDV: function(dvtype, submissionid, coursemoduleid) {
          var that = this;
          var dvWindow = window.open('', 'turnitin_viewer');

          var loading = '<div class="tii_dv_loading" style="text-align:center;">';
          loading += '<img src="' + M.cfg.wwwroot + '/plagiarism/turnitin/pix/tiiIcon.svg" style="width:100px; height: 100px">';
          loading += '<p style="font-family: Arial, Helvetica, sans-serif;">' + M.str.plagiarism_turnitin.loadingdv + '</p>';
          loading += '</div>';
          $(dvWindow.document.body).html(loading);

          // Get html to launch DV.
          $.ajax({
              type: "POST",
              url: M.cfg.wwwroot + "/plagiarism/turnitin/ajax.php",
              dataType: "json",
              data: {
                  action: "get_dv_html",
                  submissionid: submissionid,
                  dvtype: dvtype,
                  cmid: coursemoduleid,
                  sesskey: M.cfg.sesskey
              },
              success: function(data) {
                  $(dvWindow.document.body).html(loading + data);
                  dvWindow.document.forms[0].submit();
                  dvWindow.document.close();

                  that.checkDVClosed(submissionid, coursemoduleid, dvWindow);
              }
          });
        },

        checkDVClosed: function(submissionid, coursemoduleid, dvWindow) {
            var that = this;

            if (dvWindow.closed) {
                that.refreshScores(submissionid, coursemoduleid);
            } else {
                setTimeout( function(){
                    that.checkDVClosed(submissionid, coursemoduleid, dvWindow);
                }, 500);
            }
         },

         refreshScores: function(submission_id, coursemoduleid) {
              $.ajax({
                  type: "POST",
                  url: M.cfg.wwwroot + "/plagiarism/turnitin/ajax.php",
                  dataType: "json",
                  data: {
                      action: "update_grade",
                      submission: submission_id,
                      cmid: coursemoduleid,
                      sesskey: M.cfg.sesskey
                  },
                  success: function() {
                      window.location = window.location;
                  }
              });
         }
    };
});
