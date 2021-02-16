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
 * Customize modal for gugcat
 * 
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry'],
        function($, CustomEvents, Modal, ModalRegistry) {

    var registered = false;
    var SELECTORS = {
        RELEASE_PROVISIONAL_GRADE_BUTTON: '[data-action="release"]',
        RELEASE_FINAL_GRADE_BUTTON: '[data-action="finalrelease"]',
        IMPORT_GRADE_BUTTON: '[data-action="importgrades"]',
        ADJUST_WEIGHT_BUTTON: '[data-action="adjustweight"]',
        CANCEL_BUTTON: '[data-action="cancel"]',
    };

    /**
     * Constructor for the Modal.
     *
     * @param {object} root The root jQuery element for the modal
     */
    var ModalGcat = function(root) {
        Modal.call(this, root);
    };

    ModalGcat.TYPE = 'local_gugcat-gcat';
    ModalGcat.prototype = Object.create(Modal.prototype);
    ModalGcat.prototype.constructor = ModalGcat;

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    ModalGcat.prototype.registerEventListeners = function() {
        // Apply parent event listeners.
        Modal.prototype.registerEventListeners.call(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.RELEASE_PROVISIONAL_GRADE_BUTTON, function(e, data) {
            // Add your logic for when the login button is clicked. This could include the form validation,
            // loading animations, error handling etc.
            document.getElementById('release-submit').click();
        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.RELEASE_FINAL_GRADE_BUTTON, function(e, data) {
            document.getElementById('finalrelease-submit').click();
        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.IMPORT_GRADE_BUTTON, function(e, data) {
            document.getElementById('importgrades-submit').click();
        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.ADJUST_WEIGHT_BUTTON, function(e, data) {
            document.getElementById('coursegradeform-submit').click();
        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, function(e, data) {
            // Add your logic for when the cancel button is clicked.
            setInterval(() => {
                this.hide();
            }, 400);
        }.bind(this));
    };

    // Automatically register with the modal registry the first time this module is imported so that you can create modals
    // of this type using the modal factory.
    if (!registered) {
        ModalRegistry.register(ModalGcat.TYPE, ModalGcat, 'local_gugcat/modal_gcat');
        registered = true;
    }

    return ModalGcat;
});