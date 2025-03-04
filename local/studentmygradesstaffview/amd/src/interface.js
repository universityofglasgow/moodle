
import {get_string as getString} from 'core/str';
import {get_strings as getStrings} from 'core/str';

import {call as fetchMany} from 'core/ajax';

export const init = ({courseid}) => {

    // Create a SMGSF 'namespace' and bind this to window
    // this is so we can get Moodle core functions through the
    // backdoor to Vue.
    //
    // Vue's main.js has a check that this exists before the Vue
    // app is instantiated.
    window.SMGSF = {};
    window.SMGSF.courseid = courseid;
    window.SMGSF.getString = getString;
    window.SMGSF.getStrings = getStrings;

    window.SMGSF.fetchMany = fetchMany;
};