<?php
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
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add Personal Goals to course navigation.
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context_course $context
 */
function local_personalgoals_extend_navigation_course($navigation, $course, $context): void {
    if (!isloggedin() || isguestuser()) {
        return;
    }

    if (has_capability('local/personalgoals:viewown', $context)) {
        $navigation->add(
            get_string('pluginname', 'local_personalgoals'),
            new moodle_url('/local/personalgoals/index.php', ['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_personalgoals'
        );
    }
}
