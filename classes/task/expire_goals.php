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

namespace local_personalgoals\task;

use local_personalgoals\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class expire_goals.
 */
final class expire_goals extends \core\task\scheduled_task {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('taskexpiregoals', 'local_personalgoals');
    }

    /**
     * Method execute.
     *
     * @return void Return value.
     */
    public function execute(): void {
        global $DB;
        $now = time();
        $ids = $DB->get_fieldset_select(
            'local_personalgoals_goals',
            'id',
            'status = :status AND timeend > 0 AND timeend < :now',
            ['status' => api::STATUS_ACTIVE, 'now' => $now]
        );
        foreach ($ids as $id) {
            api::expire_goal((int)$id);
        }
    }
}
