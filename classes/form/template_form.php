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

namespace local_personalgoals\form;

use local_personalgoals\goal_type_manager;
use moodleform;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class template_form.
 */
final class template_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    protected function definition(): void {
        $mform = $this->_form;
        $courseid = (int)$this->_customdata['courseid'];

        $mform->addElement('text', 'name', get_string('templatename', 'local_personalgoals'), ['maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('textarea', 'description', get_string('templatedescription', 'local_personalgoals'), ['rows' => 3]);
        $mform->setType('description', PARAM_TEXT);

        $types = [];
        foreach (goal_type_manager::get_available_types($courseid) as $key => $type) {
            $types[$key] = $type['name'];
        }
        $mform->addElement('select', 'goaltype', get_string('goaltype', 'local_personalgoals'), $types);
        $mform->addElement('text', 'targetvalue', get_string('targetvalue', 'local_personalgoals'));
        $mform->setType('targetvalue', PARAM_FLOAT);
        $mform->addRule('targetvalue', null, 'required', null, 'client');

        $mform->addElement('select', 'periodtype', get_string('period', 'local_personalgoals'), [
            'daily' => get_string('perioddaily', 'local_personalgoals'),
            'weekly' => get_string('periodweekly', 'local_personalgoals'),
            'monthly' => get_string('periodmonthly', 'local_personalgoals'),
            'none' => get_string('periodnone', 'local_personalgoals'),
        ]);

        $activities = [];
        foreach (get_fast_modinfo($courseid)->get_cms() as $cm) {
            if ($cm->visible) {
                $activities[$cm->id] = format_string($cm->name);
            }
        }
        $select = $mform->addElement('select', 'cmids', get_string('specificactivities', 'local_personalgoals'), $activities, [
            'multiple' => 'multiple',
            'size' => min(10, max(4, count($activities))),
        ]);
        $select->setMultiple(true);
        $mform->hideIf('cmids', 'goaltype', 'neq', 'specific_activities');

        $mform->addElement('header', 'rewardsheader', get_string('completionrewards', 'local_personalgoals'));
        $mform->addElement('text', 'rewardxp', get_string('rewardxp', 'local_personalgoals'));
        $mform->setType('rewardxp', PARAM_INT);
        $mform->setDefault('rewardxp', 0);
        $mform->addElement('text', 'rewardcredits', get_string('rewardcredits', 'local_personalgoals'));
        $mform->setType('rewardcredits', PARAM_INT);
        $mform->setDefault('rewardcredits', 0);
        $mform->addElement('advcheckbox', 'celebrate', get_string('celebratecompletion', 'local_personalgoals'));
        $mform->setDefault('celebrate', 1);
        $mform->addElement('advcheckbox', 'repeatrewardallowed', get_string('repeatrewardallowed', 'local_personalgoals'));
        $mform->addHelpButton('repeatrewardallowed', 'repeatrewardallowed', 'local_personalgoals');
        $mform->addElement('advcheckbox', 'active', get_string('templateactive', 'local_personalgoals'));
        $mform->setDefault('active', 1);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(true, get_string('savetemplate', 'local_personalgoals'));
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
        if ((int)($data['rewardxp'] ?? 0) < 0) {
            $errors['rewardxp'] = get_string('nonnegativevalue', 'local_personalgoals');
        }
        if ((int)($data['rewardcredits'] ?? 0) < 0) {
            $errors['rewardcredits'] = get_string('nonnegativevalue', 'local_personalgoals');
        }
        return $errors;
    }
}
