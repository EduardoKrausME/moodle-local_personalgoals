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
 * dashboard.js
 *
 * @package   local_personalgoals
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str', 'core/notification'], function(Str, Notification) {
    const SELECTORS = {
        cancel: '[data-action="cancel-goal"]'
    };

    const confirmCancel = function(event) {
        const link = event.currentTarget;
        event.preventDefault();
        Promise.all([
            Str.get_string('cancelconfirmtitle', 'local_personalgoals'),
            Str.get_string('cancelconfirmbody', 'local_personalgoals'),
            Str.get_string('cancelgoal', 'local_personalgoals'),
            Str.get_string('cancel')
        ]).then(function(strings) {
            Notification.confirm(strings[0], strings[1], strings[2], strings[3], function() {
                window.location.href = link.href;
            });
            return true;
        }).catch(Notification.exception);
    };

    const init = function() {
        document.querySelectorAll(SELECTORS.cancel).forEach(function(link) {
            link.addEventListener('click', confirmCancel);
        });
    };

    return {init: init};
});
