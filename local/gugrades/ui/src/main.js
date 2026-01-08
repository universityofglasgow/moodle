import { createApp, reactive } from 'vue'
import App from './App.vue'
import Toast  from "vue-toastification";
import "vue-toastification/dist/index.css";
import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { plugin, defaultConfig } from '@formkit/vue';
import { Modal } from '@kouts/vue-modal';
import { createPinia } from 'pinia';
import { usePopulateTrees } from '../src/js/setuptrees.js';
import '../src/assets/VueModal.css';
import '../src/assets/MyGrades.css';

import customConfig from '../formkit.config.js'

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
const toastoptions = {
    position: 'top-center',
    timeout: 5000,
};

// Pinia.
const pinia = createPinia();

// Trees.
const populatetrees = usePopulateTrees();

ensureGUIsSet(timeout)
.then(() => {
    const app = createApp(App);
    app.use(pinia);
    const mstrings = reactive([]);
    app.provide('mstrings', mstrings);
    app.use(Toast, toastoptions);
    app.use(plugin, defaultConfig({
        config: customConfig.config
    }));
    app.component('EasyDataTable', Vue3EasyDataTable);
    app.component('VueModal', Modal);
    app.mount('#app');

    // Remove tiles course format class
    // Document.body.classList.remove('format-tiles');

    // Read strings
    // Strings are pushed to individual components using provide() / inject()
    const GU = window.GU;
    const fetchMany = GU.fetchMany;

    fetchMany([{
        methodname: 'local_gugrades_get_all_strings',
        args: {
        }
    }])[0]
    .then((result) => {
        const strings = result;
        strings.forEach((string) => {
            mstrings[string.tag] = string.stringvalue;
        });
    })
    .catch((error) => {
        window.console.error(error);
    });

    // Populate activity trees.
    populatetrees.populate();

});


