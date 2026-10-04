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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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
use local_personalgoals\goal_type_manager;
use local_personalgoals\service\presenter;

$courseid = optional_param('courseid', 0, PARAM_INT);
require_login($courseid ?: null);

if ($courseid > 0) {
    $course = get_course($courseid);
    $context = context_course::instance($courseid);
    require_capability('local/personalgoals:viewown', $context);
    $PAGE->set_course($course);
    $PAGE->set_context($context);
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$PAGE->set_url(new moodle_url('/local/personalgoals/index.php', $courseid ? ['courseid' => $courseid] : []));
$PAGE->set_title(get_string('mygoals', 'local_personalgoals'));
$PAGE->set_heading(get_string('mygoals', 'local_personalgoals'));
$PAGE->requires->js_call_amd('local_personalgoals/dashboard', 'init');

$goals = api::get_user_goals($USER->id, $courseid ?: null);
$active = [];
foreach ($goals as $goal) {
    $card = presenter::goal($goal);
    if ($card['status'] === api::STATUS_ACTIVE) {
        $active[] = $card;
    }
}

$suggestions = [];
if ($courseid > 0) {
    $templates = $DB->get_records('local_personalgoals_template', ['courseid' => $courseid, 'active' => 1], 'timecreated DESC');
    foreach ($templates as $template) {
        $suggestions[] = [
            'id' => (int)$template->id,
            'name' => format_string($template->name),
            'description' => format_text($template->description, FORMAT_PLAIN),
            'type' => goal_type_manager::get_type_name($template->goaltype, $courseid),
            'target' => presenter::format_value($template->goaltype, (float)$template->targetvalue),
            'period' => get_string('period' . $template->periodtype, 'local_personalgoals'),
            'accepturl' => (new moodle_url('/local/personalgoals/action.php', [
                'action' => 'accept', 'id' => $template->id, 'sesskey' => sesskey(),
            ]))->out(false),
        ];
    }
}

$data = [
    'courseid' => $courseid,
    'activegoals' => $active,
    'hasactivegoals' => !empty($active),
    'suggestions' => $suggestions,
    'hassuggestions' => !empty($suggestions),
    'cancreate' => $courseid > 0 && has_capability('local/personalgoals:manageown', $context),
    'creategoalurl' => $courseid > 0 ? (new moodle_url('/local/personalgoals/edit.php',
        ['courseid' => $courseid]))->out(false) : '',
    'historyurl' => (new moodle_url('/local/personalgoals/history.php', $courseid ?
        ['courseid' => $courseid] : []))->out(false),
    'canmanage' => $courseid > 0 && has_capability('local/personalgoals:managetemplates', $context),
    'manageurl' => $courseid > 0 ? (new moodle_url('/local/personalgoals/manage.php',
        ['courseid' => $courseid]))->out(false) : '',
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_personalgoals/dashboard', $data);
echo $OUTPUT->footer();
