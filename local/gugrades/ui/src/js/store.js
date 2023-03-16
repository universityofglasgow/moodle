/**
 * Simple store for global data
 */

import { defineStore } from 'pinia';
import {ref} from "vue";

export const useStringsStore = defineStore('stringstore', () => {
    const mstrings = ref({});

    function addString(name, stringtext) {
        mstrings.value[name] = stringtext;
    }

    return {mstrings, addString}
})