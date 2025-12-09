/* eslint-disable max-len */
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
 * @module local_ltiusage/pagination
 * @copyright 2025 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define(['jquery', 'core/ajax'], function($, ajax) {
    'use strict';

    var init = function() {

        $(document).off('click.pagination').on('click.pagination', '.pagination a', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();

            var link = $(this);

            // Skip disabled links.
            if (link.hasClass('disabled') || link.closest('li').hasClass('disabled')) {
                return false;
            }

            var url = link.attr('href');
            var tableContainer = link.closest('.lti-usage-table-group');
            var pageMatch = url.match(/page_(\d+)=(\d+)/);
            if (!pageMatch) {
                return false;
            }
            var typeId = parseInt(pageMatch[1]);
            var pageNum = parseInt(pageMatch[2]);

            // Show loading indicator.
            tableContainer.find('table').css('opacity', '0.5');

            // Call external service.
            ajax.call([{
                methodname: 'local_ltiusage_get_pagination',
                args: {
                    typeid: typeId,
                    page: pageNum,
                    perpage: 25
                },
                done: function(response) {

                    // Update table content with new data.
                    var tbody = tableContainer.find('tbody');
                    tbody.empty();

                    response.rows.forEach(function(row) {
                        var deleteCell = response.candelete ?
                            '<td><a href="' + row.deletelink + '" onclick="return confirm(\'Are you sure you want to delete this LTI activity?\');">Delete</a></td>' :
                            '';

                        var tr = '<tr>' +
                            '<td>' + row.course + '</td>' +
                            '<td>' + row.name + '</td>' +
                            '<td>' + row.visible + '</td>' +
                            '<td><a href="' + row.link + '">Open</a></td>' +
                            deleteCell +
                            '</tr>';
                        tbody.append(tr);
                    });

                    // Update pagination if needed - regenerate the entire navigation.
                    var paginationUl = tableContainer.find('.pagination');
                    if (paginationUl.length) {
                        var totalPages = Math.ceil(response.total / 25);
                        var html = '';

                        // First button.
                        html += '<li class="page-item' + (pageNum === 0 ? ' disabled' : '') + '">';
                        html += '<a href="' + url.replace(/page_\d+=\d+/, 'page_' + typeId + '=0') + '" class="page-link' + (pageNum === 0 ? ' disabled' : '') + '">&laquo; First</a>';
                        html += '</li>';

                        // Previous button.
                        if (response.has_prev) {
                            html += '<li class="page-item">';
                            html += '<a href="' + url.replace(/page_\d+=\d+/, 'page_' + typeId + '=' + response.prev) + '" class="page-link">&lsaquo; Previous</a>';
                            html += '</li>';
                        }

                        // Page info.
                        html += '<li class="page-item disabled">';
                        html += '<span class="page-link">Page ' + (pageNum + 1) + ' of ' + totalPages + '</span>';
                        html += '</li>';

                        // Next button.
                        if (response.has_next) {
                            html += '<li class="page-item">';
                            html += '<a href="' + url.replace(/page_\d+=\d+/, 'page_' + typeId + '=' + response.next) + '" class="page-link">Next &rsaquo;</a>';
                            html += '</li>';
                        }

                        // Last button.
                        html += '<li class="page-item' + (pageNum === totalPages - 1 ? ' disabled' : '') + '">';
                        html += '<a href="' + url.replace(/page_\d+=\d+/, 'page_' + typeId + '=' + (totalPages - 1)) + '" class="page-link' + (pageNum === totalPages - 1 ? ' disabled' : '') + '">Last &raquo;</a>';
                        html += '</li>';

                        paginationUl.html(html);
                    }

                    // Restore opacity.
                    tableContainer.find('table').css('opacity', '1');

                    // Scroll to table top.
                    setTimeout(function() {
                        $(window).scrollTop(tableContainer.offset().top - 20);
                    }, 10);
                },
            }]);

            return false;
        });
    };

    // Make the module globally available for js_init_call
    M.local_ltiusage = M.local_ltiusage || {};
    M.local_ltiusage.pagination = {init: init};

    return {init: init};
});