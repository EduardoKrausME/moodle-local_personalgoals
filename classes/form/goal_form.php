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

namespace local_personalgoals\form;

use local_personalgoals\api;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class goal_form.
 */
final class goal_form extends \moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    protected function definition(): void {
        $mform = $this->_form;
        $courseid = (int)$this->_customdata['courseid'];
        $userid = (int)$this->_customdata['userid'];

        $mform->addElement('text', 'name', get_string('goalname', 'local_personalgoals'), ['maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $typeoptions = [];
        foreach (api::get_available_goal_types($courseid) as $type) {
            $typeoptions[$type['key']] = $type['name'];
        }
        $mform->addElement('select', 'goaltype', get_string('goaltype', 'local_personalgoals'), $typeoptions);
        $mform->addRule('goaltype', null, 'required', null, 'client');

        $mform->addElement('text', 'targetvalue', get_string('targetvalue', 'local_personalgoals'));
        $mform->setType('targetvalue', PARAM_FLOAT);
        $mform->addRule('targetvalue', null, 'required', null, 'client');
        $mform->addRule('targetvalue', null, 'numeric', null, 'client');

        $periods = [
            'daily' => get_string('perioddaily', 'local_personalgoals'),
            'weekly' => get_string('periodweekly', 'local_personalgoals'),
            'monthly' => get_string('periodmonthly', 'local_personalgoals'),
            'custom' => get_string('periodcustom', 'local_personalgoals'),
            'none' => get_string('periodnone', 'local_personalgoals'),
        ];
        $mform->addElement('select', 'periodtype', get_string('period', 'local_personalgoals'), $periods);
        $mform->setDefault('periodtype', 'weekly');

        $mform->addElement('date_time_selector', 'customstart', get_string('customstart', 'local_personalgoals'), ['optional' => true]);
        $mform->addElement('date_time_selector', 'customend', get_string('customend', 'local_personalgoals'), ['optional' => true]);
        $mform->hideIf('customstart', 'periodtype', 'neq', 'custom');
        $mform->hideIf('customend', 'periodtype', 'neq', 'custom');

        $activities = [];
        foreach (get_fast_modinfo($courseid, $userid)->get_cms() as $cm) {
            if ($cm->uservisible && !$cm->is_stealth()) {
                $activities[$cm->id] = format_string($cm->name);
            }
        }
        $select = $mform->addElement('select', 'cmids', get_string('specificactivities', 'local_personalgoals'), $activities, [
            'multiple' => 'multiple',
            'size' => min(10, max(4, count($activities))),
        ]);
        $select->setMultiple(true);
        $mform->hideIf('cmids', 'goaltype', 'neq', 'specific_activities');

        $mform->addElement('static', 'privacyhint', '', get_string('selfregulationhint', 'local_personalgoals'));
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        $this->add_action_buttons(true, get_string('creategoal', 'local_personalgoals'));
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return array Return value.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if ((float)($data['targetvalue'] ?? 0) <= 0) {
            $errors['targetvalue'] = get_string('targetmustbepositive', 'local_personalgoals');
        }
        if (($data['goaltype'] ?? '') === 'course_completion' && (float)$data['targetvalue'] > 100) {
            $errors['targetvalue'] = get_string('percentagecannotexceed100', 'local_personalgoals');
        }
        if (($data['goaltype'] ?? '') === 'specific_activities' && empty($data['cmids'])) {
            $errors['cmids'] = get_string('selectatleastoneactivity', 'local_personalgoals');
        }
        if (($data['periodtype'] ?? '') === 'custom') {
            $start = (int)($data['customstart'] ?? 0);
            $end = (int)($data['customend'] ?? 0);
            if ($end <= 0) {
                $errors['customend'] = get_string('customendrequired', 'local_personalgoals');
            } else if ($start > 0 && $end <= $start) {
                $errors['customend'] = get_string('invaliddeadline', 'local_personalgoals');
            }
        }
        return $errors;
    }
}
