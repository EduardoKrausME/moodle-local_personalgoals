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

use local_personalgoals\api;
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
    $PAGE->set_context(context_system::instance());
}
$PAGE->set_url(new moodle_url('/local/personalgoals/history.php', $courseid ? ['courseid' => $courseid] : []));
$PAGE->set_title(get_string('goalhistory', 'local_personalgoals'));
$PAGE->set_heading(get_string('goalhistory', 'local_personalgoals'));

$goals = api::get_user_goals($USER->id, $courseid ?: null);
$cards = array_map(static fn($goal) => presenter::goal($goal), $goals);
$months = presenter::monthly_history($goals);
$data = [
    'goals' => $cards,
    'hasgoals' => !empty($cards),
    'months' => $months,
    'hasmonths' => !empty($months),
    'backurl' => (new moodle_url('/local/personalgoals/index.php', $courseid ? ['courseid' => $courseid] : []))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_personalgoals/history', $data);
echo $OUTPUT->footer();
