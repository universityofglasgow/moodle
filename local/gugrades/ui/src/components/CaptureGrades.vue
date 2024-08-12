
<!--
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language EN
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
-->

<template>
    <td>{{ firstgrade }} BLAH</td>
</template>

<script setup>
    import {ref, onMounted, defineProps, computed} from '@vue/runtime-core';
    import { getstrings } from '@/js/getstrings.js';

    const props = defineProps({
        grades: Object
    });

    const strings = ref({});

    /**
     * Computed property to show FIRST grade if there is one
     */
    const firstgrade = computed(() => {
        if (props.grades['FIRST'] == undefined) {
            return strings.value.awaitingcapture;
        } else {
            return props.grades['FIRST'].grade;
        }
    });

    /**
     * Load strings (mostly for table) and get initial data for table.
     */
     onMounted(() => {

        // Get the moodle strings for this page
        const stringslist = [
            'awaitingcapture',
        ];
        getstrings(stringslist)
        .then(results => {
            Object.keys(results).forEach((name) => {strings.value[name] = results[name]});
        })
        .catch((error) => {
            window.console.error(error);
        });
     });

</script>