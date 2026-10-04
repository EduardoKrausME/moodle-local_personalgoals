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

use moodleform;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class limit_form.
 */
final class limit_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    protected function definition(): void {
        $mform = $this->_form;
        $courseid = (int)$this->_customdata['courseid'];
        $goaltype = (string)$this->_customdata['goaltype'];

        $mform->addElement('static', 'typename', get_string('goaltype', 'local_personalgoals'), $goaltype);
        $mform->addElement('advcheckbox', 'enabled', get_string('goaltypeenabled', 'local_personalgoals'));
        $mform->addElement('text', 'mintarget', get_string('mintarget', 'local_personalgoals'));
        $mform->setType('mintarget', PARAM_FLOAT);
        $mform->addElement('text', 'maxtarget', get_string('maxtarget', 'local_personalgoals'));
        $mform->setType('maxtarget', PARAM_FLOAT);
        $mform->addElement('text', 'maxactive', get_string('maxactive', 'local_personalgoals'));
        $mform->setType('maxactive', PARAM_INT);
        $mform->addElement('advcheckbox', 'allowcustomdates', get_string('allowcustomdates', 'local_personalgoals'));
        $mform->addElement('text', 'maxdurationdays', get_string('maxdurationdays', 'local_personalgoals'));
        $mform->setType('maxdurationdays', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'goaltype', $goaltype);
        $mform->setType('goaltype', PARAM_ALPHANUMEXT);
        $this->add_action_buttons(true, get_string('savelimits', 'local_personalgoals'));
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
        if ((float)$data['mintarget'] < 0) {
            $errors['mintarget'] = get_string('nonnegativevalue', 'local_personalgoals');
        }
        if ((float)$data['maxtarget'] < (float)$data['mintarget']) {
            $errors['maxtarget'] = get_string('maxmustbegreaterthanmin', 'local_personalgoals');
        }
        if ((int)$data['maxactive'] < 1) {
            $errors['maxactive'] = get_string('positivevalue', 'local_personalgoals');
        }
        return $errors;
    }
}
