import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Toast  from "vue-toastification";
import "vue-toastification/dist/index.css";

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

// Toast defaults
const options = {
    position: 'top-center',
    timeout: 5000,
};


ensureGUIsSet(timeout)
.then(() => {
    createApp(App).use(router).use(Toast, options).mount('#app');
});


