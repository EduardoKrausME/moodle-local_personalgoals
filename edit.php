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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use core\output\notification;
use local_personalgoals\api;
use local_personalgoals\form\goal_form;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/personalgoals:manageown', $context);

$PAGE->set_url(new moodle_url('/local/personalgoals/edit.php', ['courseid' => $courseid]));
$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('creategoal', 'local_personalgoals'));
$PAGE->set_heading(get_string('creategoal', 'local_personalgoals'));

$form = new goal_form(null, ['courseid' => $courseid, 'userid' => $USER->id]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/personalgoals/index.php', ['courseid' => $courseid]));
}
if ($data = $form->get_data()) {
    $config = ['cmids' => array_map('intval', $data->cmids ?? [])];
    api::create_goal(
        $USER->id,
        $courseid,
        $data->goaltype,
        $data->name,
        (float)$data->targetvalue,
        $config,
        $data->periodtype,
        !empty($data->customstart) ? (int)$data->customstart : null,
        !empty($data->customend) ? (int)$data->customend : null
    );
    redirect(
        new moodle_url('/local/personalgoals/index.php', ['courseid' => $courseid]),
        get_string('goalcreated', 'local_personalgoals'),
        null,
        notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
