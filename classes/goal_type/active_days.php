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

namespace local_personalgoals\goal_type;

/**
 * Class active_days.
 */
final class active_days extends base_goal_type {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('goaltype_active_days', 'local_personalgoals');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        return get_string('goaltype_active_days_desc', 'local_personalgoals');
    }

    /**
     * Method get_current_value.
     *
     * @return float Return value.
     */
    public function get_current_value(): float {
        global $DB;
        $params = [
            'userid' => (int)$this->goal->userid,
            'courseid' => (int)$this->goal->courseid,
            'start' => (int)$this->goal->timestart,
        ];
        $endsql = '';
        if ((int)$this->goal->timeend > 0) {
            $endsql = ' AND timecreated <= :endtime';
            $params['endtime'] = (int)$this->goal->timeend;
        }
        return (float)$DB->count_records_sql(
            'SELECT COUNT(1) FROM {local_personalgoals_days}
              WHERE userid = :userid AND courseid = :courseid AND timecreated >= :start' . $endsql,
            $params
        );
    }
}
