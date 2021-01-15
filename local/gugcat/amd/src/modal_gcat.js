define(['jquery', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry'],
        function($, CustomEvents, Modal, ModalRegistry) {

    var registered = false;
    var SELECTORS = {
        RELEASE_PROVISIONAL_GRADE_BUTTON: '[data-action="release"]',
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