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

namespace local_personalgoals\goal_type;

use local_personalgoals\service\course_progress;

/**
 * Class course_completion.
 */
final class course_completion extends base_goal_type {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('goaltype_course_completion', 'local_personalgoals');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        return get_string('goaltype_course_completion_desc', 'local_personalgoals');
    }

    /**
     * Method get_current_value.
     *
     * @return float Return value.
     */
    public function get_current_value(): float {
        return course_progress::percentage((int)$this->goal->userid, (int)$this->goal->courseid);
    }

    /**
     * Method validate_configuration.
     *
     * @param array $config Parameter config.
     * @return array Return value.
     */
    public function validate_configuration(array $config): array {
        if ($this->get_target_value() > 100) {
            return ['targetvalue' => get_string('percentagecannotexceed100', 'local_personalgoals')];
        }
        return [];
    }
}
