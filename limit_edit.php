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

use local_personalgoals\form\limit_form;
use local_personalgoals\goal_type_manager;
use local_personalgoals\service\limits;

$courseid = required_param('courseid', PARAM_INT);
$goaltype = required_param('goaltype', PARAM_ALPHANUMEXT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/personalgoals:managelimits', $context);
if (!isset(goal_type_manager::get_types()[$goaltype])) {
    throw new moodle_exception('unknowngoaltype', 'local_personalgoals');
}

$PAGE->set_url(new moodle_url('/local/personalgoals/limit_edit.php', ['courseid' => $courseid, 'goaltype' => $goaltype]));
$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('editlimits', 'local_personalgoals'));
$PAGE->set_heading(get_string('editlimits', 'local_personalgoals'));

$form = new limit_form(null, ['courseid' => $courseid, 'goaltype' => $goaltype]);
$form->set_data(limits::get($courseid, $goaltype));
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/personalgoals/manage.php', ['courseid' => $courseid]));
}
if ($data = $form->get_data()) {
    $now = time();
    $record = $DB->get_record('local_personalgoals_limits', ['courseid' => $courseid, 'goaltype' => $goaltype]);
    $values = (object)[
        'courseid' => $courseid,
        'goaltype' => $goaltype,
        'enabled' => !empty($data->enabled) ? 1 : 0,
        'mintarget' => max(0, (float)$data->mintarget),
        'maxtarget' => max(0, (float)$data->maxtarget),
        'maxactive' => max(1, (int)$data->maxactive),
        'allowcustomdates' => !empty($data->allowcustomdates) ? 1 : 0,
        'maxdurationdays' => max(0, (int)$data->maxdurationdays),
        'timemodified' => $now,
    ];
    if ($record) {
        $values->id = $record->id;
        $DB->update_record('local_personalgoals_limits', $values);
    } else {
        $values->timecreated = $now;
        $DB->insert_record('local_personalgoals_limits', $values);
    }
    redirect(new moodle_url('/local/personalgoals/manage.php',
        ['courseid' => $courseid]), get_string('limitssaved', 'local_personalgoals'));
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
