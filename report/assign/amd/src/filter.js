define(['jquery', 'core/modal_factory', 'core/templates', 'core/str'], function($, ModalFactory, Templates, str) {

    return {
        init: function() {

            $(document).on('change', '#groupfilter', function() {
                var group = $(this).val();
                $('.group_' + group).show();
                $('.group_0').not('.group_' + group).hide();
            });


            $(".doextension").on("click", function() {
                var id = $(this).data('id')
                var fullname = $(this).data('fullname')
                var daycount = 0
                console.log("clicked " + id)
                var strings = [
                    {key: 'extensionpopuptitle', component: 'report_assign'}
                ]
                str.get_strings(strings)
                .then(function(result) {
                    console.log(result)
                    return ModalFactory.create({
                        title: result[0],
                        body: Templates.render('report_assign/extensionpopup', {
                            id: id,
                            fullname: fullname
                        }),
                    })
                })
                .then(function(modal) {
                    modal.show()

                    // handle modal buttons
                    $(".extensioninc, .extensiondec").on("click", function() {
                        var inc = $(this).is('.extensioninc')
                        if (inc && (daycount < 7)) {
                            daycount++
                        } 
                        if (!inc && (daycount > 0)) {
                            daycount--
                        }
                        $(".extensioncounter").html(daycount)
                        console.log(daycount)
                        //modal.hide()

                        return false
                    })

                    $(".extensioncancel").on("click", function() {
                        modal.hide()

                        return false
                    })

                    $(".extensionsave").on("click", function() {
                        modal.hide()

                        return false
                    })

                })
                return false
            })


        }
    };
});
