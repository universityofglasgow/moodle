// This file is part of course-quick-access plugin for Moodle
// http://moodle.org/
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

// * @copyright  2012 Thomas Heinz <mail@th1z.net>
// * @webseite   http://www.th1z.net/projects/moodle-cqa/

M.block_course_quick_access = {

};

M.block_course_quick_access.init = function(Y, categorytoggleurl, hiddencourses, hiddencoursesnotloggedin, loggedin) {
//, 
    var hiddencourses_string = hiddencourses;
    var hiddencoursesnotloggedin_string = hiddencoursesnotloggedin;
    var category_toggle_url = categorytoggleurl;
    var loggedin = loggedin;

    /**
     * Sends a request to the "category_tree_toggle.php" file to store
     * the current category-state (opened/close) in the database.
     *
     * @param node The category node, whichs state will be stored in the db.
     */
    var sendAjaxTreeToggleRequest = function(node, open) {
        // Get category id from the elements HTML-tag-id
        var id_string_array = node.get('id').split('-');
        var category_id = id_string_array[id_string_array.length-1];
        var uri = category_toggle_url;//'/blocks/course_quick_access/ajax/category_tree_toggle.php'
        var openuri = uri + '?id=' + category_id + '&state=open';
        var closeuri = uri + '?id=' + category_id + '&state=close';
        //Y.on('io:complete', ajaxTreeToggleComplete, Y, []);
        if (open==true) {
            var request = Y.io(openuri);
        } else {
            var request = Y.io(closeuri);
        }
    };

    /**
     * Restores the default visibility of all the blocks category nodes.
     * Nodes with the class "qa-hidden" will be hidden.
     */
    var restoreVisibility = function() {
        targetNode = Y.one('#qa-blockcontent');
        targetNode.all('*').each(function(node) {
            node.show();
        });
        targetNode.all('.qa-hidden').hide();
    }
    restoreVisibility(); // Call it once after a page load

    /**
     * Toggles the current state of a category node (opened/closed).
     *
     */
    var toggleTreeNode = function(e) {
        var targetNode = e.currentTarget;
        child_list = targetNode.next('.qa-courselist');
        if (child_list) {
            // Set visibility
            child_list.toggleClass('qa-hidden');
            // Show corresponding icon
            targetNode.all('.qa-icon-opened').toggleClass('qa-hidden');
            targetNode.all('.qa-icon-closed').toggleClass('qa-hidden');
            // Set opened/closed class
            if (targetNode.hasClass('qa-toggle-closed')) { // TODO: move
                targetNode.removeClass('qa-toggle-closed');
                targetNode.addClass('qa-toggle-opened');
                // Send ajax request
                sendAjaxTreeToggleRequest(targetNode, true);
            } else if (targetNode.hasClass('qa-toggle-opened')) {
                targetNode.removeClass('qa-toggle-opened');
                targetNode.addClass('qa-toggle-closed');
                // Send ajax request
                sendAjaxTreeToggleRequest(targetNode, false);
            }
            restoreVisibility();

        }
    };
    // Open/Close category on click
    Y.all('#qa-blocklist .qa-toggle').on('click', toggleTreeNode);
    // Change course-type on category-node hover
    Y.all('#qa-blocklist .qa-toggle').setStyle('cursor', 'pointer');

    /**
     * Hides all courses categories that do not contain the string that is
     * entered in the blocks search-field
     *
     */
    var onSearchInputChange = function(e) {
        var search_node = e.currentTarget;
        var element_nodes = Y.all('#qa-blocklist .qa-listentry');
        var list_headline_node = Y.one('#qa-blockcontenthead');
        var search_node_value = search_node.get('value').toLowerCase();
        search_node_value = search_node_value.replace(/[^A-Za-z0-9]/g, '');
        // Make all elements visible if there is nothing in the search box
        if(search_node_value == '') {
            restoreVisibility();
            return;
        }
        // Show a headline that informes the user that not all courses are
        // visible
        if(loggedin == 'true') {
            list_headline_node.setContent(hiddencourses_string);
        } else {
            list_headline_node.setContent(hiddencoursesnotloggedin_string);
        }
        list_headline_node.show();
        // Hide all list elements and show only the matching ones
        element_nodes.hide();
        // Show nodes containing the input value string
        element_nodes.each(function(node) {
            if(node.get('id').search(search_node_value) != -1) {
                // In case search-node was a course and its parent category was
                // hidden: Show all its ancestor
                ancestors = node.ancestors('.qa-courselist, .qa-listentry');
                if (ancestors) {ancestors.show();}
                // Show search-node
                node.show();
                // In case search-node was a cateogry: Show all its children
                children = node.all('.qa-courselist, .qa-listentry');
                if (children) {
                    children.each(function(node) {
                        node.show();
                    });
                }
            }
        });
    };
    // On search-input-field key release: Show and hide specific categories
    Y.one('.qa-search').on('keyup', onSearchInputChange);

};
