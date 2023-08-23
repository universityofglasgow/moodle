var templatestepper;

var $local_template = jQuery.noConflict();

$local_template(document).ready(function() {
    debugger;
    init();
    $local_template(window).on('resize orientationChange', init());
});

function init() {
    debugger;

    initStepper();
    initSliders();

    $local_template(document).on('click', '.add-template', function() {
        debugger;
        $local_template("#templatecourseid").val = $local_template(this).attr("data-id");
        var value = $local_template(this).attr("data-id");
        var inputselector = '#fitem_id_templatecourseid .form-autocomplete-suggestions li[data-value="' + value + '"]';
        templatestepper.next();
        $local_template(inputselector).trigger('click');
        return false;
    });

    $local_template("#templatesteppertrigger1").on('click', function() {
        debugger;
        $local_template('.slider').each(function () {
            debugger;
            var slider = $local_template(this);
            slider[0].slick.refresh();
            resizeFixes(slider);
        });
    });

    $local_template("h1.slider-heading").on('click', function() {
        debugger;
        var sliderheading = $local_template(this);
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
};

function initStepper() {
    debugger;
    templatestepper = new Stepper(document.querySelector('#templatestepper'), {
        linear: false,
        animation: true
    });

    if ($local_template("#id_shortname").val()) {
        templatestepper.to(2);
    }
}

function initSliders() {
    debugger;

    // Initialise all the sliders.
    $local_template('.slider').each(function() {
        debugger;
        var slider = $local_template(this);
        // Hide the sliders and show the loading spinner.
        slider.css('opacity', 0.01).slideUp();
        $local_template.when(slider.not('.slick-initialized').slick()).then(
            waitForFinalEvent(function() {
                debugger;
                showSlider(slider);
                slider.slideUp();
            }, 1000, "showSlider" + slider.attr("id"))
        );
    });
}

function showSlider(slider) {
    debugger;
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
        debugger;
        var card = $local_template(this);
        var cardbody = card.find('div.card-body');
        var cardoverlay = card.find('div.card-img-overlay');
        cardbody.hide();
        cardoverlay.fadeIn();
    });
}

// Add listeners to document for slick slide mouseenter.
$local_template(document).on('mouseenter', '.slick-slide', function() {
    debugger;
    var slide = $local_template(this);
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
$local_template(document).on('mouseleave', '.slick-slide', function() {
    debugger;
    var slide = $local_template(this);
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
    debugger;
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
    debugger;
    slider.find('.slick-track').each(function() {
        debugger;
        fixTrackHeight(this, animate);
    });
}

function fixTrackHeight(track, animate) {
    debugger;
    var list = $local_template(track).parent();
    var prev = list.parent().find(".slick-prev");
    var next = list.parent().find(".slick-next");
    var trackheight = $local_template(track).find(".slick-active").find(".card-img-top").height() + 60;
    if (trackheight < 260) {
        trackheight = 260;
    }
    var arrowheight = (trackheight - 60) / 2;

    $local_template(track).css("overflow-y", 'unset');

    if (animate) {
        $local_template(track).animate({
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
        $local_template(track).height(trackheight);
        prev.css("top", arrowheight + 'px');
        next.css("top", arrowheight + 'px');
    }

}