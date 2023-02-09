import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// This stuff makes sure that the window.GU variable
// exists.
// This can take some time as Moodle runs this once the page
// has loaded
var timeout = 1000000;

function ensureGUIsSet(timeout) {
    var start = Date.now();
    return new Promise(waitForGU);


    function waitForGU(resolve, reject) {
        if (window.GU) {
            resolve(window.GU)
        } else if (timeout && (Date.now() - start) >= timeout) {
            reject(new Error("timeout"));
        } else {
            setTimeout(waitForGU.bind(this, resolve, reject), 30);
        }
    }
}


ensureGUIsSet(timeout)
.then(() => {
    createApp(App).use(router).mount('#app');
});


