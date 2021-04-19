require(['jquery'], function($) { $(document).ready(function(){
    if(typeof window.speechSynthesis !== 'undefined') {
    	$('p, h1, h2, h3, h4, h5, span, a, label').hover(
    	    function() {
        	    $(this).addClass('currentlySpeaking');
        	    if(typeof speakTimer !== 'undefined') {
        	        clearTimeout(speakTimer);
        	    }
        	    window.speechSynthesis.cancel();
        	    var speakText = $(this).text();
        	    speakTimer = setTimeout( function() {
            		var msg = new SpeechSynthesisUtterance(speakText);
                	msg.rate = 0.8;
                	msg.lang = 'en-scottish';
                	$(this).addClass('currentlySpeakingHighlight');
            		window.speechSynthesis.speak(msg);
            		$(this).removeClass('currentlySpeaking');
                }, 1500);
            },
            function() {
                window.speechSynthesis.cancel();
                $(this).removeClass('currentlySpeaking');
                $(this).removeClass('currentlySpeakingHighlight');
            }
        );
    } else {
        $('<div class="alert alert-danger">Read To Me doesn\'t work in Internet Explorer because it doesn\'t support Text To Speech. We recommend you use Google Chrome or Firefox instead if you want to use Read To Me.</div>').insertBefore("#page-content");
    }
})});