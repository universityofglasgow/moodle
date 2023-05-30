debugger;
var templatestepper;

$(document).ready(function() {
    debugger;
    initStepper();
    initSliders();

    $(document).on('click', '.add-template', function() {
        debugger;
        $("#templatecourseid").val = $(this).attr("data-id");
        var value = $(this).attr("data-id");
        var inputselector = '#fitem_id_templatecourseid .form-autocomplete-suggestions li[data-value="' + value + '"]';
        templatestepper.next();
        $(inputselector).trigger('click');
        return false;
    });

    $("#templatesteppertrigger1").on('click', function() {
        $('.slider').each(function () {
            var slider = $(this);
            slider[0].slick.refresh();
            resizeFixes(slider);
        });
    });

    $("h1.slider-heading").on('click', function() {
        debugger;
        var sliderheading = $(this);
        var slider = sliderheading.nextAll('.slider:first');
        if (sliderheading.hasClass('collapsed')) {
            sliderheading.removeClass('collapsed');
            showSlider(slider);
        } else {
            sliderheading.addClass('collapsed');
            slider.slideUp();
            slider.hide();
        }
    });
});


function initStepper() {
    templatestepper = new Stepper(document.querySelector('#templatestepper'), {
        linear: false,
        animation: true
    });

    debugger;
    if ($("#id_shortname").val()) {
        templatestepper.to(2);
    }
}

function initSliders() {
    // wrapper.js

    debugger;

    // Initialise all the sliders.
    $('.slider').each(function() {

        debugger;

        var slider = $(this);
        // Hide the sliders and show the loading spinner.
        slider.css('opacity', 0.01).slideUp();
        debugger;
        $.when(slider.not('.slick-initialized').slick()).then(
            waitForFinalEvent(function() {
                debugger;
                showSlider(slider);
                slider.slideUp();
            }, 1000, "showSlider" + slider.attr("id"))
        );
    });
}

function showSlider(slider) {
    var spinner = slider.parent().find(".spinner");

    slider.show();
    slider[0].slick.refresh();
    resizeFixes(slider, false);

    spinner.hide();
    slider.css('opacity', 1).hide();
    slider.slideDown({
        queue: false,
        duration: 'slow'
    });

    // For each card in the slider, hide the body and footer, and animate the progress bar.
    slider.find('.card').each(function() {
        var card = $(this);
        var cardbody = card.find('div.card-body');
        var cardoverlay = card.find('div.card-img-overlay');
        cardbody.hide();
        cardoverlay.fadeIn();
    });
}


// Add listeners to document for slick slide mouseenter.
$(document).on('mouseenter', '.slick-slide', function() {

    var slide = $(this);
    var card = slide.find('div.card');
    var cardbody = card.find('div.card-body');
    var cardoverlay = card.find('div.card-img-overlay');

    // On mouseenter, bring the current slide up to 100% opacity, and scale by 1.2x.
    slide.css('animation-name', 'fadein-scaleup');

    // Use jquery transitions for revealing cardbody and cardfooter.
    cardbody.stop().slideDown();
    cardoverlay.stop().fadeOut();
});

// Add listeners for slick slide mouseleave.
$(document).on('mouseleave', '.slick-slide', function() {

    var slide = $(this);
    var card = slide.find('div.card');
    var cardbody = card.find('div.card-body');
    var cardoverlay = card.find('div.card-img-overlay');

    // On mouseleave, return the current slide up to 75% opacity, and reset the scale to 1x.
    slide.css('animation-name', 'fadeout-scaledown');

    // Use jquery transitions for hiding cardbody.
    cardbody.stop().slideUp();
    cardoverlay.stop().fadeIn();
});

var waitForFinalEvent = (function() {
    var timers = {};
    return function(callback, ms, uniqueId) {
        if (!uniqueId) {
            uniqueId = "Don't call this twice without a uniqueId";
        }
        if (timers[uniqueId]) {
            clearTimeout(timers[uniqueId]);
        }
        timers[uniqueId] = setTimeout(callback, ms);
    };
})();

function resizeFixes(slider, animate) {
    slider.find('.slick-track').each(function() {
        fixTrackHeight(this, animate);
    });
}

function fixTrackHeight(track, animate) {
    var list = $(track).parent();
    var prev = list.parent().find(".slick-prev");
    var next = list.parent().find(".slick-next");
    var trackheight = $(track).find(".slick-active").find(".card-img-top").height() + 60;
    debugger;
    if (trackheight < 260) {
        trackheight = 260;
    }
    var arrowheight = (trackheight - 60) / 2;

    $(track).css("overflow-y", 'unset');

    if (animate) {
        $(track).animate({
            queue: false,
            duration: 'slow',
            'height': trackheight + 'px'
        }, 100);
        prev.animate({
            queue: false,
            duration: 'slow',
            'top': arrowheight + 'px'
        }, 100);
        next.animate({
            queue: false,
            duration: 'slow',
            'top': arrowheight + 'px'
        }, 100);
    } else {
        $(track).height(trackheight);
        prev.css("top", arrowheight + 'px');
        next.css("top", arrowheight + 'px');
    }

}