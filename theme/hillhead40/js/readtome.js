/**
 * Reimplenting this script using plain ol javascript as Moodle doesn't want us using jQuery anymore.
 * Basically, when Read-To-Me has been turned on, this should search the page for particular elements
 * and, using the text value, run these through the browser's text to speech feature. The async approach
 * now gets us around the (subtle) problem with the previous version by waiting for ^all^ the content to
 * have loaded, before starting the search. See Student MyGrades for an example, the 'dashboard' panel
 * loads separately to the rest of the page.
 * @param {int} timeout
 * @returns {Promise<*|null>}
 */
async function waitForElement(timeout = 2000) {
    console.log('waitForElement called...');
    const start = Date.now();

    while (Date.now() - start < timeout) {
        if(typeof window.speechSynthesis !== 'undefined') {
			document.querySelectorAll('p, h1, h2, h3, h4, h5, span, a, label, button, strong, canvas, table>thead>tr>th, div.border div.alert.alert-info, a[href*="downloadspdetails.php"]>i').forEach((v) => {
				v.addEventListener('mouseover', (e) => {
					e.target.classList.add('currentlySpeaking', 'currentlySpeakingHighlight');
					if(typeof speakTimer !== 'undefined') {
						clearTimeout(speakTimer);
					}
					window.speechSynthesis.cancel();
					let speakText = ((e.target.ariaLabel != null) ? e.target.ariaLabel : e.target.textContent);
        	        let $hillheadCurrentlySpeaking = e.target;
        	        speakTimer = setTimeout( function() {
            		    var msg = new SpeechSynthesisUtterance(speakText);
                	    msg.lang = 'en-gb';
                	    msg.onend = function(event) {
                    	    $hillheadCurrentlySpeaking.classList.remove('currentlySpeaking', 'currentlySpeakingHighlight');
                	    };
            		    window.speechSynthesis.speak(msg);
                    }, 1500);
				});
				v.addEventListener('mouseout', (e) => {
					window.speechSynthesis.cancel();
					e.target.classList.remove('currentlySpeaking', 'currentlySpeakingHighlight');
				});
			  });
		} else {
			let tempPanel = document.querySelector('#page-content');
			tempPanel.insertAdjacentHTML("beforebegin", '<div class="alert alert-danger">Read To Me is unavailable in your browser as it doesn\'t appear to support Text To Speech. We recommend you use Google Chrome or Firefox instead if you want to use Read To Me.</div>');
		}
        await new Promise(resolve => setTimeout(resolve, 1000));
    }

    return null;
}

waitForElement(2500);