<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use local_personalgoals\api;

$action = required_param('action', PARAM_ALPHA);
$id = required_param('id', PARAM_INT);
require_login();
require_sesskey();

switch ($action) {
    case 'accept':
        $template = $DB->get_record('local_personalgoals_template', ['id' => $id], '*', MUST_EXIST);
        $course = get_course($template->courseid);
        require_login($course);
        require_capability('local/personalgoals:manageown', context_course::instance($course->id));
        api::accept_template($id, $USER->id);
        redirect(new moodle_url('/local/personalgoals/index.php',
            ['courseid' => $course->id]), get_string('templateaccepted', 'local_personalgoals'));
    case 'cancel':
        $goal = $DB->get_record('local_personalgoals_goals', ['id' => $id], '*', MUST_EXIST);
        $course = get_course($goal->courseid);
        require_login($course);
        api::cancel_goal($id);
        redirect(new moodle_url('/local/personalgoals/index.php',
            ['courseid' => $course->id]), get_string('goalcancellednotice', 'local_personalgoals'));
    case 'repeat':
        $goal = $DB->get_record('local_personalgoals_goals', ['id' => $id], '*', MUST_EXIST);
        $course = get_course($goal->courseid);
        require_login($course);
        require_capability('local/personalgoals:manageown', context_course::instance($course->id));
        api::recreate_goal($id);
        redirect(new moodle_url('/local/personalgoals/index.php',
            ['courseid' => $course->id]), get_string('goalrecreated', 'local_personalgoals'));
    default:
        throw new moodle_exception('invalidaction');
}
