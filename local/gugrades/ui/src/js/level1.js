/**
 * This composable stores the selected Level 1 category
 * allowing it to be automagically selected again on tab
 * change.  
 */

import _ from 'underscore';

var level1store = 0;

/**
 * Set the level 1 on various tabs
 */
export function setlevel1(level) {
    level1store = Number(level);
}

/**
 * Get level1, ensuring it's valid
 */
export function getlevel1(validcats) {

    // Extract id fields
    const ids = validcats.map((cat) => cat.id);

    if (_.contains(ids, level1store)) {
        return level1store;
    } else {
        return 0;
    }
}