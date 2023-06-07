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
 * Modal drop setup.
 *
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/templates', 'core/modal', 'core/modal_events', 'core/notification', 'block_xp/role-button'], function(
    Templates,
    Modal,
    ModalEvents,
    Notification,
    RoleButton
) {
    // Trigger pre-loading.
    Templates.render('local_xp/modal-drop-setup', []);

    /**
   * Show the modal.
   *
   * @param {Object} context The template context.
   */
    function show(context) {
        Templates.render('local_xp/modal-drop-setup', context)
            .then((html) => {
                const modal = new Modal(html);

                // Broadcast when the modal has been shown.
                modal.getRoot().on(ModalEvents.shown, () => {
                    const codeNode = modal.getRoot().find('[data-content="shortcode"]')[0];
                    const copyBtn = modal.getRoot().find('[data-action="copy"]')[0];
                    if (!codeNode || !copyBtn) {
                        return;
                    } else if (!navigator.clipboard) {
                        copyBtn.style = 'display: none';
                        return;
                    }

                    copyBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        navigator.clipboard.writeText(codeNode.textContent);
                    });
                });

                modal.show();
                return;
            })
            .catch(Notification.exception);
    }

    /**
   * Show the modal from a node selector.
   *
   * @param {String} nodeSelector The node selector.
   */
    function showFromSelector(nodeSelector) {
        const node = document.querySelector(nodeSelector);
        if (!node) {
            return;
        }
        showFromNode(node);
    }

    /**
   * Show the modal from a node.
   *
   * @param {Node} node The node
   */
    function showFromNode(node) {
        const name = node.dataset.name;
        const shortcode = node.dataset.shortcode;
        const editurl = node.dataset.editurl;
        show({name, shortcode, editurl});
    }

    /**
   * Delegate the modal.
   *
   * @param {String} rootSelector The root selector.
   * @param {String} nodeSelector The node selector.
   */
    function delegateClick(rootSelector, nodeSelector) {
        RoleButton.delegateClick(rootSelector, nodeSelector, (node) => {
            showFromNode(node);
        });
    }

    return {show, showFromNode, showFromSelector, delegateClick};
});
