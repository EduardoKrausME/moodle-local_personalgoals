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

use local_personalgoals\form\template_form;
use local_personalgoals\service\json;

$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/personalgoals:managetemplates', $context);

$PAGE->set_url(new moodle_url('/local/personalgoals/template_edit.php', ['courseid' => $courseid, 'id' => $id]));
$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('edittemplate', 'local_personalgoals'));
$PAGE->set_heading(get_string('edittemplate', 'local_personalgoals'));

$form = new template_form(null, ['courseid' => $courseid]);
if ($id) {
    $record = $DB->get_record('local_personalgoals_template', ['id' => $id, 'courseid' => $courseid], '*', MUST_EXIST);
    $config = json::decode($record->configjson);
    $record->cmids = $config['cmids'] ?? [];
    $record->rewardxp = $config['rewardxp'] ?? 0;
    $record->rewardcredits = $config['rewardcredits'] ?? 0;
    $record->celebrate = array_key_exists('celebrate', $config) ? (int)$config['celebrate'] : 1;
    $record->repeatrewardallowed = (int)($config['repeatrewardallowed'] ?? 0);
    $form->set_data($record);
}
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/personalgoals/manage.php', ['courseid' => $courseid]));
}
if ($data = $form->get_data()) {
    $now = time();
    $config = [
        'cmids' => array_values(array_map('intval', $data->cmids ?? [])),
        'rewardxp' => max(0, (int)$data->rewardxp),
        'rewardcredits' => max(0, (int)$data->rewardcredits),
        'celebrate' => !empty($data->celebrate),
        'repeatrewardallowed' => !empty($data->repeatrewardallowed),
    ];
    $record = (object)[
        'courseid' => $courseid,
        'goaltype' => $data->goaltype,
        'name' => trim($data->name),
        'description' => trim($data->description),
        'targetvalue' => $data->goaltype === 'specific_activities' ? count($config['cmids']) : (float)$data->targetvalue,
        'periodtype' => $data->periodtype,
        'configjson' => json::encode($config),
        'active' => !empty($data->active) ? 1 : 0,
        'timemodified' => $now,
    ];
    if ($id) {
        $record->id = $id;
        $DB->update_record('local_personalgoals_template', $record);
    } else {
        $record->timecreated = $now;
        $DB->insert_record('local_personalgoals_template', $record);
    }
    redirect(new moodle_url('/local/personalgoals/manage.php', ['courseid' => $courseid]),
        get_string('templatesaved', 'local_personalgoals'));
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
