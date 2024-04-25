// Jquery already defined in current context.
/* global $, Stepper */
/* jslint latedef:false */
/* eslint-disable no-unused-vars */
/* jshint unused:false */
/* eslint-disable no-debugger */
/* jshint debug: true */

var templatestepper;
var templateselector = '#templatestepper';
var idshortnameselector = '#id_shortname';
var coursedetailsstep = 2;
var sliderselector = '.slider';
var usetemplateselector = '.add-template';
var idtemplatecourseselector = '#fitem_id_templatecourseid';
var sliderinitcount = 0;

// We are not in a moodle AMD context, so use jquery in no conflict mode.
// var $local_template = jQuery.noConflict();

/**
 * Wait until the page is loaded before initialising.
 */
$(document).ready(function() {
    local_template_init();
});

$( window ).on( "resize", function() {
    local_template_resizeFixes();
} );

/**
 * Initialise.
 */
function local_template_init() {

    // If Stepper exists, initialise stepper.
    if ($(templateselector).length) {
        local_template_initStepper();
    }

    // If sliders exist, register event for when sliders finish loading, and initialise sliders.
    if ($(sliderselector).length) {
        $(sliderselector).on('init', function (event, slick) {
            sliderinitcount += 1;
            // Have all sliders been initialised?
            if ($(sliderselector).length === sliderinitcount) {
                // local_template_registerSliderEvents();
            }
        });

        local_template_initSliders();
    }

    local_template_registerEvents();
}

/**
 * Initialise the stepper component.
 */
function local_template_initStepper() {

    // Initialise stepper.
    if ($(templateselector).length) {
        templatestepper = new Stepper(document.querySelector(templateselector), {
            linear: false,
            animation: true
        });

        // Automatically goto the second step of the stepper if the shortname already exists. e.g. loading a saved template.
        if ($(idshortnameselector).val()) {
            templatestepper.to(coursedetailsstep);
        }
    }
}

/**
 * Initialise the slick sliders.
 */
function local_template_initSliders() {

    // Initialise all the sliders.
    $(sliderselector).each(function() {
        var slider = $(this);
        // Hide the sliders and show the loading spinner.
        slider.css('opacity', 0.01).slideUp();
        $.when(slider.not('.slick-initialized').slick()).then(
            local_template_showSlider(slider) //, slider.slideUp()
        );
        slider.slideDown();

        /*
                $.when(slider.not('.slick-initialized').slick()).then(
                    waitForFinalEvent(function() {
                        showSlider(slider);
                        slider.slideUp();
                    }, 2000, "showSlider" + slider.attr("id"))
                );

         */
    });

    /*
        elementLoaded('.slick-initialized', $('.slider').length, function(element) {
            // Element is ready to use.
            registerMouseEvents();
        });
     */

}

/**
 * Register events
 */
function local_template_registerEvents() {
    // document or body?
    $('body').on('click', usetemplateselector, function() {

        var value = $(this).attr("data-id");
        var inputselector = idtemplatecourseselector + ' .form-autocomplete-suggestions li[data-value="' + value + '"]';
        $("#templatecourseid").val = value;

        if (templatestepper !== undefined) {
            templatestepper.next();
        }
        $(inputselector).trigger('click');
        if ($('#id_coursedetailscontainer').length) {
            if (!$('#id_coursedetailscontainer').hasClass('show')) {
                $('a[href="#id_coursedetailscontainer"]').trigger('click');
            }
        }
        document.querySelector(idtemplatecourseselector).scrollIntoView({behaviour: "auto", block: "start"}); //{block: "end",inline: "nearest"}
    });

    $("#templatesteppertrigger1").on('click', function() {
        $(sliderselector).each(function () {
            var slider = $(this);
            slider[0].slick.refresh();
            local_template_resizeFixes(slider);
        });
    });

    $("h1.category-heading").on('click', function() {
        var categoryheading = $(this);
        var carddeck = categoryheading.nextAll('.card-deck:first');
        if (!carddeck.hasClass('slider')) return;

        if (categoryheading.hasClass('collapsed')) {
            if (carddeck.hasClass('slider')) {
                local_template_showSlider(carddeck);
            } else {
                carddeck.show();
            }
        } else {
            carddeck.slideUp();
        }
    });
}
/*
// Wait for element to exist.
function local_template_elementLoaded(element, count, callback) {
    if ($(element).length == count) {
        // All elements loaded.
        callback($(element));
    } else {
        // Repeat every 500ms.
        setTimeout(function() {
            elementLoaded(element, count, callback)
        }, 50);
    }
};
*/

/*
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
*/

function local_template_resizeFixes(slider, animate) {
    var tracks = slider.find('.slick-track');
    if (typeof tracks !== "undefined") {
        tracks.each(function() {
            local_template_fixTrackHeight(this, animate);
        });
    }
}

function local_template_showSlider(slider) {
    var spinner = slider.parent().find(".spinner");

    slider.show();
    slider[0].slick.refresh();
    local_template_resizeFixes(slider, false);

    if (typeof spinner !== "undefined") spinner.hide();
    slider.css('opacity', 1).hide();
    slider.slideDown({
        queue: false,
        duration: 'slow'
    });

    // For each card in the slider, hide the body and footer.
    var cards = slider.find('.card');
    if (typeof cards !== "undefined") {
        slider.find('.card').each(function () {
            var card = $(this);
            var cardbody = card.find('div.card-body');
            var cardoverlay = card.find('div.card-img-overlay');
            cardbody.hide();
            cardoverlay.fadeIn();
        });
    }
}

function local_template_registerSliderEvents(track) {
    // Add listeners to document for slick slide mouseenter.
    // document or body?

    // Add listeners for slick slide mouseleave.
    $(track).on('mouseleave', '.slick-slide', function() {
        var slide = $(this);
        var card = slide.find('div.card');
        var cardbody = card.find('div.card-body');
        var cardoverlay = card.find('div.card-img-overlay');

        // On mouseleave, return the current slide up to 75% opacity, and reset the scale to 1x.
        //slide.css('animation-delay', '0s');
        //slide.css('animation-duration', '500ms');
        slide.css('animation-name', 'fadeout-scaledown');

        // Use jquery transitions for hiding cardbody.
        cardbody.stop().slideUp();
        cardoverlay.stop().fadeIn();
    });

    $(track).on('mouseenter', '.slick-slide', function() {
        var slide = $(this);
        var card = slide.find('div.card');
        var cardbody = card.find('div.card-body');
        var cardoverlay = card.find('div.card-img-overlay');

        // On mouseenter, bring the current slide up to 100% opacity, and scale by 1.2x.
        //slide.css('animation-delay', '500ms');
        slide.css('animation-name', 'fadein-scaleup');

        // Use jquery transitions for revealing cardbody and cardfooter.
        cardbody.stop().slideDown();
        cardoverlay.stop().fadeOut();
    });
}

function local_template_fixTrackHeight(track, animate) {
    var list = $(track).parent();
    var prev = list.parent().find(".slick-prev");
    var next = list.parent().find(".slick-next");
    var trackheight = $(track).find(".slick-active").find(".card-img-top").height() + 60;
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

    local_template_registerSliderEvents(track);

}