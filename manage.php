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

use local_personalgoals\goal_type_manager;
use local_personalgoals\service\limits;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/personalgoals:managetemplates', $context);

$PAGE->set_url(new moodle_url('/local/personalgoals/manage.php', ['courseid' => $courseid]));
$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('managegoals', 'local_personalgoals'));
$PAGE->set_heading(get_string('managegoals', 'local_personalgoals'));

$templates = [];
foreach ($DB->get_records('local_personalgoals_template', ['courseid' => $courseid], 'timecreated DESC') as $template) {
    $templates[] = [
        'id' => $template->id,
        'name' => format_string($template->name),
        'goaltype' => goal_type_manager::get_type_name($template->goaltype, $courseid),
        'active' => (bool)$template->active,
        'activelabel' => $template->active ? get_string('yes') : get_string('no'),
        'editurl' => (new moodle_url('/local/personalgoals/template_edit.php', ['courseid' => $courseid, 'id' => $template->id]))->out(false),
    ];
}

$limitrows = [];
if (has_capability('local/personalgoals:managelimits', $context)) {
    foreach (goal_type_manager::get_types() as $key => $classname) {
        $dummy = (object)['id' => 0, 'userid' => $USER->id, 'courseid' => $courseid, 'targetvalue' => 1, 'configjson' => null];
        $instance = new $classname($dummy);
        $limit = limits::get($courseid, $key);
        $limitrows[] = [
            'key' => $key,
            'name' => $instance->get_name(),
            'enabled' => (bool)$limit->enabled,
            'enabledlabel' => $limit->enabled ? get_string('yes') : get_string('no'),
            'range' => format_float($limit->mintarget, 0) . ' – ' . format_float($limit->maxtarget, 0),
            'maxactive' => (int)$limit->maxactive,
            'editurl' => (new moodle_url('/local/personalgoals/limit_edit.php', ['courseid' => $courseid, 'goaltype' => $key]))->out(false),
        ];
    }
}

$data = [
    'templates' => $templates,
    'hastemplates' => !empty($templates),
    'limits' => $limitrows,
    'haslimits' => !empty($limitrows),
    'newtemplateurl' => (new moodle_url('/local/personalgoals/template_edit.php', ['courseid' => $courseid]))->out(false),
    'backurl' => (new moodle_url('/local/personalgoals/index.php', ['courseid' => $courseid]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_personalgoals/manage', $data);
echo $OUTPUT->footer();
